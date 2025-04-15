<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Perwalian PDF</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 30px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Page 1: Agenda Perwalian -->
    <div class="header">
        <h2>Agenda Perwalian</h2>
    </div>
    <div class="section">
        <p><strong>Kelas:</strong> {{ $perwalian->kelas }}</p>
        <p>
            <strong>Tanggal:</strong>
            {{ \Carbon\Carbon::parse($beritaAcara->tanggal_perwalian)->translatedFormat('l, d F Y') }}
        </p>
        <p><strong>Agenda:</strong></p>
        <div style="border: 1px solid #333; padding: 10px;">
            {!! nl2br(e($beritaAcara->agenda_perwalian ?? 'Tidak ada agenda')) !!}
        </div>
    </div>
    <div class="footer">
        <p>Dosen Wali</p>
    </div>

    <!-- Page 2: Berita Acara Perwalian -->
    <div class="page-break"></div>
    <div class="header">
        <h2>Berita Acara Perwalian</h2>
    </div>
    <div class="section">
        <p><strong>Kelas:</strong> {{ $perwalian->kelas }}</p>
        <p>
            <strong>Tanggal:</strong>
            {{ \Carbon\Carbon::parse($beritaAcara->tanggal_perwalian)->translatedFormat('l, d F Y') }}
        </p>
        <p><strong>Berita Acara:</strong></p>
        <div style="border: 1px solid #333; padding: 10px;">
            {!! nl2br(e($beritaAcara->catatan_feedback ?? 'Tidak ada berita acara')) !!}
        </div>
    </div>
    <div class="footer">
        <p>Dosen Wali Signature: _______________________</p>
    </div>

    <!-- Page 3: Daftar Mahasiswa Absensi -->
    <div class="page-break"></div>
    <div class="header">
        <h2>Daftar Mahasiswa Absensi</h2>
    </div>
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student['nama'] }}</td>
                        <td>{{ $student['nim'] }}</td>
                        <td>
                            @if(isset($absensi[$student['nim']]))
                                {{ $absensi[$student['nim']]->status_kehadiran }}
                            @else
                                Belum
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="footer">
        <p>-- End of Absensi --</p>
    </div>
</body>
</html>
