@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row">
        <!-- Kolom Pengumuman -->
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

        <!-- Kolom User -->
        <div class="col-md-6">
            <div class="cards p-3">
                <h5 class="border-bottom-line text-start">DAFTAR PENGGUNA</h5>
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .cards {
        background: #fff;
        border: 1px solid #eaeaea;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .border-bottom-line {
        border-bottom: 2px solid #ddd;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }
    .pengumuman li a:hover {
        text-decoration: underline;
    }
</style>
@endsection
