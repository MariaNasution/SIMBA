@extends('layouts.app')

@section('content')
<div class="histori-page">
  <!-- Header -->
  <div class="histori-header">
    <form action="{{ route('dosen.histori') }}" method="GET" class="filter-form">
      <input type="text" name="search" placeholder="Cari..." value="{{ request('search') ?? '' }}">
      <select name="category" onchange="this.form.submit()">
        <option value="" {{ $selectedCategory == '' ? 'selected' : '' }}>Semua Kategori</option>
        <option value="semester_baru" {{ $selectedCategory == 'semester_baru' ? 'selected' : '' }}>Semester Baru</option>
        <option value="sebelum_uts" {{ $selectedCategory == 'sebelum_uts' ? 'selected' : '' }}>Sebelum UTS</option>
        <option value="sebelum_uas" {{ $selectedCategory == 'sebelum_uas' ? 'selected' : '' }}>Sebelum UAS</option>
      </select>
    </form>
  </div>

  <!-- Content: Conditional Display -->
  <div class="histori-container {{ $showSingleCategory ? 'single-category' : '' }}">
    @if($showSingleCategory)
      <!-- Single Column Display -->
      <div class="histori-column">
        <div class="histori-title">
          <h2>{{ $categoryTitle }}</h2>
        </div>
        <div class="histori-items">
          @forelse ($singleRecords as $item)
            <div class="histori-item">
              <a href="{{ route('dosen.histori.detailed', $item['ID_Perwalian']) }}">
                {{ \Carbon\Carbon::parse($item['Tanggal'])->translatedFormat('l, d F Y') }} ({{ $item['kelas'] }})
              </a>
            </div>
          @empty
            <div class="histori-item">Tidak ada data</div>
          @endforelse
        </div>
      </div>
    @else
      <!-- Three Column Display -->
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
    @endif
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
    width: 180px;
  }

  /* Style the placeholder */
  .filter-form input[type="text"]::placeholder {
    color: #B7B3B6; /* Placeholder color */
  }

  /* Style the select placeholder (default option) */
  .filter-form select {
    color: #B7B3B6; /* Default color for select */
  }

  /* Ensure the select text turns black when a non-default option is selected */
  .filter-form select option:not(:first-child) {
    color: #000; /* Black text for actual options */
  }

  /* When the input has a value, make the text black */
  .filter-form input[type="text"] {
    color: #000; /* Default text color when typing */
  }

  /* Ensure the placeholder color persists even when the input is focused or has a value */
  .filter-form input[type="text"]:focus::placeholder,
  .filter-form input[type="text"]:not(:placeholder-shown)::placeholder {
    color: #B7B3B6; /* Keep placeholder color consistent */
  }

  .histori-container {
    display: flex;
    justify-content: space-between; /* For three columns */
    gap: 1.5rem;
    flex-wrap: wrap;
  }

  /* When showing a single category, center the column */
  .histori-container.single-category {
    justify-content: center;
    align-items: center; /* Vertically center the column */
    min-height: 50vh; /* Ensure there's enough height for vertical centering */
  }

  .histori-column {
    padding-top: 50px;
    flex: 1;
    max-width: 300px;
  }

  /* Adjust the column width and alignment when showing a single category */
  .histori-container.single-category .histori-column {
    flex: 0 1 300px;
    margin-bottom: 500px;
    display: flex;
    flex-direction: column;
    align-items: center; /* Center content horizontally within the column */
    text-align: center; /* Ensure text is centered */
  }

  .histori-title {
    background: #fff;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 100%; /* Ensure the title takes the full width of the column */
  }

  .histori-title h2 {
    font-size: 1.1rem;
    color: #222;
    margin: 0;
  }

  .histori-items {
    background: transparent;
    font-family: "Poppins", sans-serif;
    width: 100%; /* Ensure items take the full width of the column */
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

    .histori-column {
      flex: 1;
      max-width: 100%;
    }

    .histori-container.single-category .histori-column {
      flex: 1;
      max-width: 100%;
    }

    .histori-container.single-category {
      min-height: auto; /* Adjust for smaller screens */
    }
  }
</style>
@endsection