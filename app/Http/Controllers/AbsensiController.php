<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {

        return view('perwalian.absensi_mahasiswa'); $classes = [
            [
                'date' => '2025-02-20',
                'class' => 'IF1',
                'formatted_date' => 'Senin, 20 Februari 2025',
                'display' => 'Senin, 20 Februari 2025 (13 IF1)'
            ],
            [
                'date' => '2025-02-21',
                'class' => 'IF2',
                'formatted_date' => 'Selasa, 21 Februari 2025',
                'display' => 'Selasa, 21 Februari 2025 (13 IF2)'
            ],
        ];
    
        return view('perwalian.absensi_mahasiswa', compact('classes'));
    }

    public function show(Request $request, $date, $class)
    {
        // In a real application, fetch attendance data from a database
        $students = []; // This would be populated from a database query

        if ($class === 'IF1') {
            $students = [
                'Yanrikho Sicilagan', 'Joel Bonar Septian Sinambela', 'Rajphael Zefanya Siahaan',
                'Pangeran Simamora', 'Olga Frischilla G.', 'Febiola Cindy Tampubolon',
                'Patricia Agustin Sibarani', 'DHEA GRACE A. SIMANJUNTAK', 'William Napitupulu',
                'Christian Theofani Napitpulu', 'Jonathan Martinus Pangaribuan', 'Baha Ambrosius Sibarani',
                'Gabriela Amelia Silitonga'
            ];
        } elseif ($class === 'IF2') {
            $students = [
                'Mario Agustin Sijabat', 'Bertrand Cornelius Sianipar', 'Roy Jonathan Hutajulu',
                'Chavvin E Melkishear Sihombing', 'JOEL CHANDIO P. C. ARITONANG', 'Glen Sofian Pardede',
                'Rohit Jayapalan Parreira Sibarani', 'Samuel Dulan Parreira Sibarani', 'Yireel Schwartz Sihaputar',
                'Frans Daniel Simarmata', 'Ferdinand Martua Shombing', 'Viktoria Maria Kristianti Lubis',
                'KRISTINA ANGGRIANI MARULIN'
            ];
        }

        $title = "Absensi Mahasiswa / IF {$class} Angkatan 2022";
        $attendanceData = []; // This would include status for each student (e.g., present, absent, permission)

        return view('perwalian.perwalianKelas', compact('title', 'students', 'date', 'class', 'attendanceData'));
    }
}
