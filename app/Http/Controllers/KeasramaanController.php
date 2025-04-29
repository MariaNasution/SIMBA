<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\StudentBehavior;

class KeasramaanController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        return view('beranda.homeKeasramaan', compact('pengumuman'));
    }

    public function pelanggaran()
    {
        return view('catatanPerilaku.catatan_perilaku_keasramaan');
    }

    public function detail($studentNim)
    {
        $apiToken = session('api_token');
        $user     = session('user');

        if (!$apiToken || ! $user || !($user['role'] === 'keasramaan' || isset($user['nim']))) {
            return redirect()->back()->withErrors(['error' => 'Session data tidak lengkap.']);
        }

        try {
            //
            // 1) Ambil Nilai Perilaku
            //
            $respNilai = Http::withToken($apiToken)
                ->withOptions(['verify'=>false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $studentNim,
                ]);

            if (! $respNilai->successful()) {
                if ($respNilai->status() != 500) {
                    return redirect()->back()
                        ->withErrors(['error'=>'Gagal mengambil data penilaian dari API.']);
                }
                $nilaiPerilaku = [];
            } else {
                $arr = $respNilai->json()['Nilai Perilaku'] ?? [];
                $nilaiPerilaku = array_values($arr);
            }

            //
            // 2) Ambil Pelanggaran API
            //
            $respPel = Http::withToken($apiToken)
                ->withOptions(['verify'=>false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-pelanggaran-mhs', [
                    'nim'    => $studentNim,
                    'ta'     => '',
                    'sem_ta' => '',
                ]);

            $pelanggaranList = [];
            if ($respPel->successful()) {
                // normalisasi: pastikan setiap record punya 'id' => null
                $raw = $respPel->json()['data'] ?? [];
                $pelanggaranList = array_map(fn($r)=>
                    is_array($r)
                        ? array_merge(['id'=>null], $r)
                        : ['id'=>null]
                , $raw);
            }

            //
            // 3) Ambil Kebaikan API
            //
            $respKeb = Http::withToken($apiToken)
                ->withOptions(['verify'=>false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-kebaikan-mhs', [
                    'nim'    => $studentNim,
                    'ta'     => '',
                    'sem_ta' => '',
                ]);

            $kebaikanList = [];
            if ($respKeb->successful()) {
                $rawK = $respKeb->json()['data'] ?? [];
                $kebaikanList = array_map(fn($r)=>
                    is_array($r)
                        ? array_merge(['id'=>null], $r)
                        : ['id'=>null]
                , $rawK);
            }

            //
            // 4) Ambil local DB behaviors, group by "TA-semester"
            //
            $localBehaviors = StudentBehavior::where('student_nim', $studentNim)
                ->get()
                ->groupBy(fn($item)=> $item->ta.'-'.$item->semester);

            //
            // 5) Proses tiap semester
            //
            foreach ($nilaiPerilaku as &$per) {
                // convert sem_ta → teks
                $per['semester'] = $this->convertSemester($per['sem_ta'] ?? 0);
                $key = $per['ta'].'-'.($per['sem_ta'] ?? 0);

                // — Pelanggaran API filter →
                $filPel = array_filter($pelanggaranList, fn($r)=>
                    (int)$r['ta']===(int)$per['ta'] &&
                    (int)$r['sem_ta']===(int)$per['sem_ta']
                );
                // — + local DB pelanggaran
                if (isset($localBehaviors[$key])) {
                    $dbPel = $localBehaviors[$key]
                        ->where('type','pelanggaran')
                        ->map(fn($i)=>[
                            'id'          => $i->id,
                            'pelanggaran' => $i->description,
                            'unit'        => $i->unit,
                            'tanggal'     => $i->tanggal,
                            'poin'        => $i->poin,
                            'tindakan'    => $i->tindakan,
                        ])->toArray();
                    $filPel = array_merge(array_values($filPel), $dbPel);
                }
                $per['pelanggaran'] = array_values($filPel);

                // — Kebaikan API filter →
                $filKeb = array_filter($kebaikanList, fn($r)=>
                    (int)$r['ta']===(int)$per['ta'] &&
                    (int)$r['sem_ta']===(int)$per['sem_ta']
                );
                // — + local DB perbuatan_baik
                $dbKeb = [];
                if (isset($localBehaviors[$key])) {
                    $dbKeb = $localBehaviors[$key]
                        ->where('type','perbuatan_baik')
                        ->map(fn($i)=>[
                            'id'             => $i->id,
                            'perbuatan_baik' => $i->description,
                            'unit'           => $i->unit,
                            'tanggal'        => $i->tanggal,
                            'poin'           => $i->poin,
                            'tindakan'       => $i->tindakan,
                        ])->toArray();
                }
                $per['perbuatan_baik'] = array_values(array_merge(array_values($filKeb), $dbKeb));

                // — Hitung akumulasi skor —
                $skAwal = 0;
                $totPel = array_sum(array_map(fn($x)=>(int)($x['poin']??0), $per['pelanggaran']));
                $totKeb = array_sum(array_map(fn($x)=>(int)($x['poin']??0), $per['perbuatan_baik']));
                $per['akumulasi_skor'] = $skAwal + $totPel - $totKeb;
            }
            unset($per);

            // 6) Kirim ke Blade
            return view('catatanPerilaku.catatan_perilaku_detail', [
                'nilaiPerilaku' => $nilaiPerilaku,
                'studentNim'    => $studentNim,
            ]);
        } catch (\Exception $e) {
            Log::error('detail() exception: '.$e->getMessage());
            return redirect()->back()
                ->withErrors(['error'=>'Terjadi kesalahan: '.$e->getMessage()]);
        }
    }

    private function convertSemester($sem_ta)
    {
        return match ($sem_ta) {
            1 => 'Gasal',
            2 => 'Genap',
            3 => 'Pendek',
            default => 'Tidak Diketahui',
        };
    }
}
