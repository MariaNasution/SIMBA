@extends('layouts.app')

@section('content')


<div class="container mt-4">
  <h3 class="text-center mb-4">Mahasiswa Aktif TA 2024</h3>

  <!-- Search Form -->
  <form method="GET" action="#" class="mb-3">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Pencarian (Kosongkan untuk Semua)"
        value="{{ request('search') }}">
      <button type="submit" class="btn btn-primary">Cari</button>
    </div>
  </form>

  @php
  // Retrieve student data from session
  $studentData = session('student_data_all_student');
  $students = $studentData['mahasiswa'] ?? [];

  // Get the search term from the query parameters
  $search = request('search', '');

  // Filter the students array if a search term is provided
  if ($search !== '') {
  $students = array_filter($students, function($student) use ($search) {
  return stripos($student['nim'], $search) !== false ||
  stripos($student['nama'], $search) !== false ||
  stripos((string)$student['angkatan'], $search) !== false ||
  stripos($student['prodi_name'], $search) !== false;
  });
  // Re-index the filtered array
  $students = array_values($students);
  }
  @endphp

  <div class="table-responsive">
    <table class="table table-borderless">
      <thead>
        <tr>
          <th>#</th>
          <th>NIM</th>
          <th class="text-start">Nama Mahasiswa</th>
          <th class="text-primary">Tahun Masuk</th>
          <th class="text-start">Program Studi</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($students as $index => $student)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $student['nim'] }}</td>
          <td class="text-start">
            <a href="{{ route('catatan_perilaku_detail', ['studentNim' => $student['nim']]) }}">
              {{ $student['nama'] }}
            </a>
          </td>
          <td>{{ $student['angkatan'] }}</td>
          <td class="text-start">{{ $student['prodi_name'] }}</td>
        </tr>
        @endforeach

        @if(count($students) === 0)
        <tr>
          <td colspan="5" class="text-center">Data tidak ditemukan.</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
</div>
@endsection