@extends('layouts.app')

@section('content')
<div class="histori-page">
  <!-- Header -->
  <div class="histori-header">
    <form action="{{ route('dosen.histori') }}" method="GET" class="filter-form">
      <input type="text" name="search" placeholder="Cari..." value="{{ request('search') ?? '' }}">
      <select name="category" onchange="this.form.submit()">
        <option value="" {{ request('category') == '' ? 'selected' : '' }}>Kategori</option>
        <option value="semester_baru" {{ request('category') == 'semester_baru' ? 'selected' : '' }}>Semester Baru</option>
        <option value="sebelum_uts" {{ request('category') == 'sebelum_uts' ? 'selected' : '' }}>Sebelum UTS</option>
        <option value="sebelum_uas" {{ request('category') == 'sebelum_uas' ? 'selected' : '' }}>Sebelum UAS</option>
      </select>
    </form>
  </div>

  <!-- Content Columns -->
  <div class="histori-container">
    <div class="histori-column">
      <div class="histori-title">
        <h2>Semester Baru</h2>
      </div>
      <div class="histori-items">
        @forelse ($semesterBaru as $item)
          <div class="histori-item">
            <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}">
              {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
            </a>
          </div>
        @empty
          <div class="histori-item">Tidak ada data</div>
        @endforelse
      </div>
    </div>

    <div class="histori-column">
      <div class="histori-title">
        <h2>Sebelum UTS</h2>
      </div>
      <div class="histori-items">
        @forelse ($sebelumUts as $item)
          <div class="histori-item">
            <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}">
              {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
            </a>
          </div>
        @empty
          <div class="histori-item">Tidak ada data</div>
        @endforelse
      </div>
    </div>

    <div class="histori-column">
      <div class="histori-title">
        <h2>Sebelum UAS</h2>
      </div>
      <div class="histori-items">
        @forelse ($sebelumUas as $item)
          <div class="histori-item">
            <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}">
              {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
            </a>
          </div>
        @empty
          <div class="histori-item">Tidak ada data</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@section('styles')
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f1f5f9;
  }

  .histori-page {
    padding: 2rem;
  }

  .histori-header {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 2rem;
  }

  .filter-form {
    display: flex;
    gap: 1rem;
    align-items: center;
  }

  .filter-form input[type="text"],
  .filter-form select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #FDFDFD;
    font-size: 0.95rem;
    color: #B7B3B6;
    width: 180px;
  }

  .histori-container {
    display: flex;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
  }

  .histori-column {
    padding-top: 50px;
    flex: 1;
    max-width: 300px;
  
  }

  .histori-title {

    background: #fff;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow only on the bottom */
    gap:20px;
  }

  .histori-title h2 {
    text-align: center;
    font-size: 1.1rem;
    color: #222;
    margin: 0; /* Remove default margin to fit within padding */
  }

  .histori-items {
    background: transparent; /* Ensure no white background */
    font-family: "Poppins", sans-serif;
  }

  .histori-item {
    background: #68B8EA;
    color: #fff;

    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.7rem;
    text-align: center;
    font-size: 0.95rem;
    transition: background 0.2s;
  }

  .histori-item:hover {
    background: #dbeafe;
  }

  .histori-item a {
    text-decoration: none;
    color: #fff;
    display: block;
    width: 100%;
  }

  @media (max-width: 768px) {
    .histori-container {
      flex-direction: column;
      gap: 1rem;
    }

    .filter-form {
      flex-direction: column;
      align-items: flex-end;
      gap: 0.5rem;
    }

    .filter-form input,
    .filter-form select {
      width: 100%;
    }
  }
</style>
@endsection