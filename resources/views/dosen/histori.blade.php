@extends('layouts.app')

@section('content')
<div class="histori-wrapper p-5 relative h-screen flex flex-col bg-gray-100">
    <!-- Search & Kategori di pojok kanan atas -->
    <div class="search-filter absolute top-0 right-0 m-2">
        <form method="GET" action="{{ route('dosen.histori') }}">
            <div class="search-group flex gap-2 items-center">
                <input type="text" name="search" placeholder="Cari..." 
                       class="form-control w-16 h-6 text-xs px-1 py-0.5 border border-gray-300 rounded" 
                       value="{{ request('search') }}">
                <select name="kategori" 
                        class="form-select w-16 h-6 text-xs px-1 py-0.5 border border-gray-300 rounded">
                    <option value="">Kategori</option>
                    <option value="uts" {{ request('kategori') == 'uts' ? 'selected' : '' }}>Sebelum UTS</option>
                    <option value="uas" {{ request('kategori') == 'uas' ? 'selected' : '' }}>Sebelum UAS</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Tabel header di dalam kotak putih -->
    <div class="histori-table-content mt-24 w-full">
        <div class="bg-white p-6 rounded shadow w-full">
            <table class="histori-table w-full border-collapse">
                <thead>
                    <tr>
                        <th class="w-1/3 text-center p-5 text-2xl font-bold text-gray-800 border-r border-gray-200">
                            Semester Baru
                        </th>
                        <th class="w-1/3 text-center p-5 text-2xl font-bold text-gray-800 border-r border-gray-200">
                            Sebelum UTS
                        </th>
                        <th class="w-1/3 text-center p-5 text-2xl font-bold text-gray-800">
                            Sebelum UAS
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Isi histori akan ditempatkan di sini -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
