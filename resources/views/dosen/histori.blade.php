@extends('layouts.app')

@section('content')
  <!-- History Page Header -->
  <div class="header">
    <h1>History</h1>
    <div class="search-container">
      <!-- We wrap the inputs in a form so we can submit the filters -->
      <form action="{{ route('dosen.histori') }}" method="GET" style="display: flex; gap: 0.5rem;">
        <input type="text" name="search" placeholder="Cari..." 
               value="{{ request('search') ?? '' }}" />
        <select name="category" onchange="this.form.submit()">
          <option value="" {{ request('category') == '' ? 'selected' : '' }}>Kategori</option>
          <option value="semester_baru" {{ request('category') == 'semester_baru' ? 'selected' : '' }}>Semester Baru</option>
          <option value="sebelum_uts" {{ request('category') == 'sebelum_uts' ? 'selected' : '' }}>Sebelum UTS</option>
          <option value="sebelum_uas" {{ request('category') == 'sebelum_uas' ? 'selected' : '' }}>Sebelum UAS</option>
        </select>
        <button type="submit" class="btn btn-primary">Cari</button>
      </form>
    </div>
  </div>

  <!-- History Page Main Content -->
  <div class="container">
    <!-- Column 1: Semester Baru -->
    <div class="column">
      <h2>Semester Baru</h2>
      @forelse ($semesterBaru as $item)
        <div class="item">
          {{-- Example: "Senin, 20 Februari 2025 (13 IF1)" --}}
          {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
        </div>
      @empty
        <div class="item">No data</div>
      @endforelse
    </div>

    <!-- Column 2: Sebelum UTS -->
    <div class="column">
      <h2>Sebelum UTS</h2>
      @forelse ($sebelumUts as $item)
        <div class="item">
          {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
        </div>
      @empty
        <div class="item">No data</div>
      @endforelse
    </div>

    <!-- Column 3: Sebelum UAS -->
    <div class="column">
      <h2>Sebelum UAS</h2>
      @forelse ($sebelumUas as $item)
        <div class="item">
          {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
        </div>
      @empty
        <div class="item">No data</div>
      @endforelse
    </div>
  </div>
@endsection

@section('styles')
  <style>
    /* Reset some default styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: sans-serif;
      /* Light background to mimic the wavy pattern (replace with an actual image/SVG if needed) */
      background: linear-gradient(135deg, #f6f9fc 0%, #eef4fb 100%);
    }

    /* Top Header / Navigation */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background-color: #008cc9;
      color: #fff;
    }

    .header h1 {
      font-size: 1.5rem;
    }

    /* Search container (right side) */
    .search-container {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .search-container input[type="text"] {
      padding: 0.5rem;
      border: none;
      border-radius: 4px;
      outline: none;
    }

    .search-container select {
      padding: 0.5rem;
      border: none;
      border-radius: 4px;
      outline: none;
    }

    .container {
      display: flex;
      justify-content: space-evenly;
      align-items: flex-start;
      margin: 2rem;
      gap: 2rem;
    }

    .column {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      width: 280px;
      min-height: 100px;
      padding: 1rem;
    }

    .column h2 {
      margin-bottom: 1rem;
      text-align: center;
      font-size: 1.2rem;
      color: #333;
    }

    .item {
      margin-bottom: 0.8rem;
      padding: 0.8rem;
      background-color: #f1f6fa;
      border-radius: 4px;
      font-size: 0.95rem;
      color: #333;
      text-align: center;
    }
  </style>
@endsection
