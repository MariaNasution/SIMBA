<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DosenController extends Controller
{
    public function beranda()
    {
        // Only allow dosen users
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        $apiToken = session('api_token');
        $nip = session('user')['username'];
        $baseUrl = 'https://cis-dev.del.ac.id';
        $students = [];
        
        // Fixed parameters (for current semester IPS selection)
        $currentTa = 2020;
        $currentSem = 2;
        
        if ($apiToken) {
            try {
                // Step 1: Fetch dosen details
                $dosenResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("{$baseUrl}/api/library-api/dosen", ['nip' => $nip]);
                
                if (!$dosenResponse->successful()) {
                    Log::error('Failed to fetch dosen data', ['status' => $dosenResponse->status()]);
                    return back()->with('error', 'Failed to fetch lecturer data.');
                }
                
                $dosenData = $dosenResponse->json();
                $dosenId = $dosenData['data']['dosen'][0]['dosen_id'] ?? null;
                if (!$dosenId) {
                    return back()->with('error', 'Dosen ID not found.');
                }
                
                // Step 2: Fetch anak wali using the get-all-students-by-dosen-wali endpoint
                $mahasiswaResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("{$baseUrl}/api/library-api/get-all-students-by-dosen-wali", [
                        'dosen_id' => $dosenId,
                        'ta'       => $currentTa,
                        'sem_ta'   => $currentSem,
                    ]);
                
                if (!$mahasiswaResponse->successful()) {
                    Log::error('Failed to fetch anak wali data', ['status' => $mahasiswaResponse->status()]);
                    return back()->with('error', 'Failed to fetch student data.');
                }
                
                $responseData = $mahasiswaResponse->json();
                if (!isset($responseData['anak_wali'])) {
                    Log::error('Anak wali data not found in response', ['response' => $responseData]);
                    return back()->with('error', 'No student data found.');
                }
                $mahasiswaData = $responseData['anak_wali'];
                
                // Step 3: For each student, fetch penilaian (cumulative) and calculate IPK and IPS
                foreach ($mahasiswaData as $student) {
                    $nim = $student['nim'] ?? null;
                    if ($nim) {
                        // Call get-penilaian without passing ta and sem_ta to get cumulative data
                        $penilaianResponse = Http::withToken($apiToken)
                            ->withOptions(['verify' => false])
                            ->get("{$baseUrl}/api/library-api/get-penilaian", [
                                'nim'     => $nim,
                                'ta'      => '',
                                'sem_ta'  => '',
                            ]);
                        
                        $penilaianData = $penilaianResponse->successful() ? $penilaianResponse->json() : [];
                        
                        // Calculate IPK as the average of all valid (numeric) IPS values
                        $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                        $sum = 0;
                        $count = 0;
                        foreach ($ipSemesterData as $entry) {
                            if (isset($entry['ip_semester']) && is_numeric($entry['ip_semester'])) {
                                $sum += floatval($entry['ip_semester']);
                                $count++;
                            }
                        }
                        $ipk = $count > 0 ? number_format($sum / $count, 2) : null;
                        
                        // Determine IPS: find the entry with the highest semester that has a numeric value
                        $validIps = [];
                        foreach ($ipSemesterData as $key => $entry) {
                            if (isset($entry['ip_semester']) && is_numeric($entry['ip_semester']) && isset($entry['sem'])) {
                                $validIps[] = $entry;
                            }
                        }
                        $ips = null;
                        if (!empty($validIps)) {
                            // Sort descending by the 'sem' field
                            usort($validIps, function($a, $b) {
                                return $b['sem'] - $a['sem'];
                            });
                            // Use the IPS from the entry with the highest semester
                            $ips = $validIps[0]['ip_semester'];
                        }
                        
                        // Set status_krs to null (API not ready)
                        $statusKrs = null;
                        $semester = $currentSem;
                        
                        $studentData = array_merge($student, [
                            'ipk'         => $ipk,
                            'ips'         => $ips,
                            'status_krs'  => $statusKrs,
                            'semester'    => $semester,
                        ]);
                        
                        $students[] = $studentData;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error fetching data in beranda:', ['message' => $e->getMessage()]);
                return back()->with('error', 'An error occurred while fetching data.');
            }
        }
        
        return view('beranda.homeDosen', compact('students'));
    }
    
    public function showDetailedClass($class)
    {
        // Only allow dosen users
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        $apiToken = session('api_token');
        $nip = session('user')['username'];
        $baseUrl = 'https://cis-dev.del.ac.id';
        $students = [];
        $currentTa = 2020;
        $currentSem = 2;
        
        if ($apiToken) {
            try {
                // Fetch dosen details
                $dosenResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("{$baseUrl}/api/library-api/dosen", ['nip' => $nip]);
                
                if (!$dosenResponse->successful()) {
                    Log::error('Failed to fetch dosen data', ['status' => $dosenResponse->status()]);
                    return back()->with('error', 'Failed to fetch lecturer data.');
                }
                
                $dosenData = $dosenResponse->json();
                $dosenId = $dosenData['data']['dosen'][0]['dosen_id'] ?? null;
                if (!$dosenId) {
                    return back()->with('error', 'Dosen ID not found.');
                }
                
                // Fetch anak wali using dosen_id
                $mahasiswaResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("{$baseUrl}/api/library-api/get-all-students-by-dosen-wali", [
                        'dosen_id' => $dosenId,
                        'ta'       => $currentTa,
                        'sem_ta'   => $currentSem,
                    ]);
                
                if (!$mahasiswaResponse->successful()) {
                    Log::error('Failed to fetch anak wali data', ['status' => $mahasiswaResponse->status()]);
                    return back()->with('error', 'Failed to fetch student data.');
                }
                
                $responseData = $mahasiswaResponse->json();
                if (!isset($responseData['anak_wali'])) {
                    Log::error('Anak wali data not found in response', ['response' => $responseData]);
                    return back()->with('error', 'No student data found.');
                }
                $mahasiswaData = $responseData['anak_wali'];
                
                // For each student, fetch penilaian (cumulative) and compute IPK and IPS
                foreach ($mahasiswaData as $student) {
                    $nim = $student['nim'] ?? null;
                    if ($nim) {
                        $penilaianResponse = Http::withToken($apiToken)
                            ->withOptions(['verify' => false])
                            ->get("{$baseUrl}/api/library-api/get-penilaian", [
                                'nim'     => $nim,
                                'ta'      => '',
                                'sem_ta'  => '',
                            ]);
                        
                        $penilaianData = $penilaianResponse->successful() ? $penilaianResponse->json() : [];
                        
                        $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                        $sum = 0;
                        $count = 0;
                        foreach ($ipSemesterData as $entry) {
                            if (isset($entry['ip_semester']) && is_numeric($entry['ip_semester'])) {
                                $sum += floatval($entry['ip_semester']);
                                $count++;
                            }
                        }
                        $ipk = $count > 0 ? number_format($sum / $count, 2) : null;
                        
                        // Determine IPS as the numeric IPS from the most recent semester available
                        $validIps = [];
                        foreach ($ipSemesterData as $key => $entry) {
                            if (isset($entry['ip_semester']) && is_numeric($entry['ip_semester']) && isset($entry['sem'])) {
                                $validIps[] = $entry;
                            }
                        }
                        $ips = null;
                        if (!empty($validIps)) {
                            usort($validIps, function($a, $b) {
                                return $b['sem'] - $a['sem'];
                            });
                            $ips = $validIps[0]['ip_semester'];
                        }
                        
                        $statusKrs = null;
                        $semester = $currentSem;
                        
                        $studentData = array_merge($student, [
                            'ipk'         => $ipk,
                            'ips'         => $ips,
                            'status_krs'  => $statusKrs,
                            'semester'    => $semester,
                        ]);
                        
                        $students[] = $studentData;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error fetching data in showDetailedClass:', ['message' => $e->getMessage()]);
                return back()->with('error', 'An error occurred while fetching data.');
            }
        }
        
        return view('dosen.detailedClass', compact('students', 'class'));
    }
    
    public function index()
    {
        // Remains unchanged for perwalian index
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        $dosenId = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenId)
            ->where('role', 'mahasiswa')
            ->select('username', 'name', 'semester', 'ipk', 'ips', 'status_krs')
            ->get()
            ->toArray();
        
        return view('dosen.index', compact('anakWali'));
    }
    
    public function presensi()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        $dosenId = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenId)
            ->where('role', 'mahasiswa')
            ->select('username', 'name', 'semester', 'ipk', 'ips', 'status_krs')
            ->get();
        
        return view('dosen.presensi', compact('anak_wali'));
    }
    
    public function setPerwalian()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        $dosenId = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenId)
            ->where('role', 'mahasiswa')
            ->select('username', 'name', 'semester', 'ipk', 'ips', 'status_krs')
            ->get()
            ->toArray();
        
        return view('perwalian.setPerwalian', compact('anakWali'));
    }
}
