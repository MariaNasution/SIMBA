@extends('layouts.app')

@section('content')
  <!-- History Page Header -->
  <div class="header">
    <div class="search-container">
      <!-- We wrap the inputs in a form so we can submit the filters -->
      <form action="{{ route('dosen.histori') }}" method="GET">
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
          <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}" class="text-decoration-none">
            {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
          </a>
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
          <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}" class="text-decoration-none">
            {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
          </a>
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
          <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}" class="text-decoration-none">
            {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
          </a>
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
      background: linear-gradient(135deg, #f6f9fc 0%, #eef4fb 100%);
    }

    .header {
      display: flex;
      justify-content: flex-end; /* Aligns the search-container to the right */
      padding: 1rem 2rem;
      color: #ddd;
    }

    .search-container {
      display: flex;
      flex-direction: column; /* Stack elements vertically */
      gap: 0.5rem;
      width: 200px; /* Match the width in the image */
    }

    .search-container input[type="text"],
    .search-container select {
      width: 100%; /* Full width of the container */
      padding: 0.5rem;
      border: 1px solid #ddd; /* Add a light border to match the image */
      border-radius: 4px;
      outline: none;
      background-color: #f5f5f5; /* Light gray background to match the image */
      color: #666; /* Text color to match the placeholder in the image */
    }

    .search-container input[type="text"]::placeholder {
      color: #999; /* Placeholder color to match the image */
    }

    .search-container .btn {
      display: none; /* Hide the button as it's not in the image */
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

    /* Optional: style links inside .item */
    .item a {
      color: #333;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .header {
        padding: 1rem;
      }

      .search-container {
        width: 100%;
        max-width: 200px; /* Keep the width consistent on smaller screens */
      }

      .container {
        flex-direction: column;
        align-items: center;
      }

      .column {
        width: 100%;
        max-width: 280px;
      }
    }
  </style>
@endsection