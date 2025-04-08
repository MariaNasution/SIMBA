@extends('layouts.app')

@section('content')
    <div class="d-flex">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

        <!-- Main Content -->
        <div class="container-fluid p-4">

            <div class="col-md-6">
                <div class="cards p-3">
                    <h5 class="border-bottom-line text-start">PENGUMUMAN</h5>
                    <ul class="list-unstyled text-start pengumuman">
                        @forelse ($pengumuman as $item)
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <a href="{{ route('konselor_pengumunankonselor.detail', $item->id) }}" class="text-decoration-none">
                                    <span>
                                        <strong
                                            class="@switch($item->sumber)
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
                                <div class="ms-3">
                                    <form method="POST" action="{{ route('konselor_pengumuman.destroy', $item->id) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');"
                                            title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="text-muted">Belum ada pengumuman.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalKalenderAkademik">Unggah Kalender Akademik</button>
                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalKalenderBEM">Unggah Kalender NonAkademik</button>
                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalPengumuman">Unggah Pengumuman</button>
            </div>

            <div class="mt-3">
                @php
                    $akademik = \App\Models\Calendar::where('type', 'akademik')->latest()->first();
                    $bem = \App\Models\Calendar::where('type', 'bem')->latest()->first();
                @endphp

                @if ($akademik)
                    <p><a href="{{ asset('storage/' . $akademik->file_path) }}" target="_blank">Lihat Kalender Akademik</a></p>
                @endif

                @if ($bem)
                    <p><a href="{{ asset('storage/' . $bem->file_path) }}" target="_blank">Lihat Kalender NonAkademik</a></p>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Kalender Akademik -->
    <div class="modal fade" id="modalKalenderAkademik" tabindex="-1" aria-labelledby="modalKalenderAkademikLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKalenderAkademikLabel">Unggah Kalender Akademik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('konselor_calendar.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="type" value="akademik">
                        <div class="mb-3">
                            <label for="fileKalenderAkademik" class="form-label">Pilih File</label>
                            <input type="file" name="file" class="form-control" id="fileKalenderAkademik" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Unggah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Kalender BEM -->
    <div class="modal fade" id="modalKalenderBEM" tabindex="-1" aria-labelledby="modalKalenderBEMLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKalenderBEMLabel">Unggah Kalender NonAkademik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('konselor_calendar.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="type" value="bem">
                        <div class="mb-3">
                            <label for="fileKalenderBEM" class="form-label">Pilih File</label>
                            <input type="file" name="file" class="form-control" id="fileKalenderBEM" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Unggah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Unggah Pengumuman -->
    <div class="modal fade" id="modalPengumuman" tabindex="-1" aria-labelledby="modalPengumumanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalPengumumanLabel">Unggah Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPengumuman" method="POST" action="{{ route('konselor_pengumuman.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sumberPengumuman" class="form-label">Sumber</label>
                            <select class="form-control" name="sumber" id="sumberPengumuman" required>
                                <option value="">Pilih Sumber</option>
                                <option value="BEM">BEM</option>
                                <option value="Info">INFO</option>
                                <option value="Bursar">BURSAR</option>
                                <option value="Keasramaan">KEASRAMAAN</option>
                                <option value="Kemahasiswaan">KEMAHASISWAAN</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="judulPengumuman" class="form-label">Judul</label>
                            <input type="text" name="judul" class="form-control" id="judulPengumuman"
                                placeholder="Masukkan judul pengumuman" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsiPengumuman" class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" id="deskripsiPengumuman" rows="4"
                                placeholder="Masukkan deskripsi pengumuman" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="uploadPengumuman">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal untuk Deskripsi Pengumuman -->
    <div class="modal fade" id="pengumumanModal" tabindex="-1" aria-labelledby="pengumumanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pengumumanModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="pengumumanDeskripsi"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pengumumanModal = document.getElementById('pengumumanModal');
            const modalTitle = document.getElementById('pengumumanModalLabel'); // Elemen judul modal
            const modalBody = document.getElementById('pengumumanDeskripsi'); // Elemen deskripsi modal

            pengumumanModal.addEventListener('show.bs.modal', function(event) {
                // Elemen yang memicu modal
                const button = event.relatedTarget;
                // Ambil data dari atribut tombol
                const judul = button.getAttribute('data-judul');
                const deskripsi = button.getAttribute('data-deskripsi');
                console.log('Judul:', judul);
                console.log('Deskripsi:', deskripsi);
                // Masukkan data ke modal
                modalTitle.textContent = judul;
                modalBody.textContent = deskripsi;
            });
        });

        function confirmLogout() {
            Swal.fire({
                title: 'Apakah anda yakin ingin keluar?',
                text: "Anda akan keluar dari akun ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, keluar!',
                cancelButtonText: 'Tidak',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('logout') }}';
                }
            });
        }
    </script>
    
@section('styles')
    <style>
        #pengumumanModal .modal-content {
            width: 50%;
            margin: 30px auto;
        }
    </style>
@endsection
@endsection
