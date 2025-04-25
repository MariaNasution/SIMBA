<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class BeritaAcaraExport implements FromCollection, WithHeadings, WithMapping
{
    protected $absensi;
    protected $columns;
    protected $prodiMap;

    public function __construct($absensi, $columns)
    {
        $this->absensi = $absensi;
        $this->columns = $columns;
        $this->prodiMap = [
            'IF' => 'S1 Informatika',
            'TRPL' => 'S1 Teknik Rekayasa Perangkat Lunak',
            'TK' => 'S1 Teknik Komputer',
            'TI' => 'S1 Teknik Informasi',
            'TB' => 'S1 Teknik Bioproses',
            'TM' => 'S1 Teknik Metalurgi',
            'SI' => 'S1 Sistem Informasi',
            'TE' => 'S1 Teknik Elektro',
            'MR' => 'S1 Manajemen Rekayasa',
        ];
    }

    public function collection()
    {
        return collect($this->absensi);
    }

    public function headings(): array
    {
        $headingsMap = [
            'nim' => 'NIM',
            'nama' => 'Nama',
            'status_kehadiran' => 'Status Kehadiran',
            'keterangan_absensi' => 'Keterangan',
            'kelas' => 'Kelas',
            'dosen_wali' => 'Dosen Wali',
            'prodi' => 'Prodi',
        ];

        return array_filter($headingsMap, fn($key) => in_array($key, $this->columns), ARRAY_FILTER_USE_KEY);
    }

    public function map($absensi): array
    {
        $row = [];
        $statusMap = [
            'hadir' => 'Hadir',
            'alpa' => 'Alpa',
            'izin' => 'Izin',
        ];

        foreach ($this->columns as $column) {
            switch ($column) {
                case 'nim':
                    $row[] = $absensi->nim ?? 'N/A';
                    break;
                case 'nama':
                    $row[] = $absensi->nama ?? 'Unknown';
                    break;
                case 'status_kehadiran':
                    $row[] = $statusMap[$absensi->status_kehadiran] ?? 'Tidak Diketahui';
                    break;
                case 'keterangan_absensi':
                    $row[] = $absensi->keterangan ?? '';
                    break;
                case 'kelas':
                    $row[] = $absensi->kelas ?? 'N/A';
                    break;
                case 'dosen_wali':
                    $row[] = $absensi->dosen_wali ?? 'N/A';
                    break;
                case 'prodi':
                    $prefix = strtok($absensi->kelas, '1234567890');
                    $row[] = $this->prodiMap[$prefix] ?? 'N/A';
                    break;
            }
        }

        return $row;
    }
}