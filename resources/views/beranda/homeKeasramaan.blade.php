@extends('layouts.app')

@section('content')
    <!-- Header -->


    <!-- Main Content: Pengumuman -->
    <div class="container-fluid p-4">
        <div class="col-md-6">
            <div class="cards p-3">
                <h5 class="border-bottom-line text-start">PENGUMUMAN</h5>
                <ul class="list-unstyled text-start pengumuman">
                    @forelse ($pengumuman as $item)
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <a href="{{ route('pengumuman.detail', $item->id) }}" class="text-decoration-none">
                                <span>
                                    <strong class="
                                        @switch($item->sumber)
                                            @case('BEM') text-primary @break
                                            @case('INFO') text-danger @break
                                            @case('BURSAR') text-info @break
                                            @case('KEASRAMAAN') text-success @break
                                            @case('KEMAHASISWAAN') text-purple @break
                                            @default text-dark
                                        @endswitch">
                                        [{{ strtoupper($item->sumber) }}]
                                    </strong>
                                    {{ $item->judul }}
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="text-muted">Belum ada pengumuman.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Example styles for the announcement card */
        .cards {
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .border-bottom-line {
            border-bottom: 2px solid #ddd;
        }
        .pengumuman li a:hover {
            text-decoration: underline;
        }
    </style>
@endsection
