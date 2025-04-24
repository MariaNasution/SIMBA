@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah User</h1>
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" class="form-control @error('role') is-invalid @enderror" id="role">
                <option value="">Pilih Role</option>
                <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                <option value="konselor" {{ old('role') == 'konselor' ? 'selected' : '' }}>Konselor</option>
                <option value="kemahasiswaan" {{ old('role') == 'kemahasiswaan' ? 'selected' : '' }}>Kemahasiswaan</option>
                <option value="keasramaan" {{ old('role') == 'keasramaan' ? 'selected' : '' }}>Keasramaan</option>
                <option value="orang_tua" {{ old('role') == 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Mahasiswa Fields -->
        <div class="form-group" id="mahasiswa-fields" style="display: none;">
            <label for="nama">Nama</label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}">
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <label for="nim">NIM</label>
            <input type="text" name="nim" class="form-control @error('nim') is-invalid @enderror" value="{{ old('nim') }}">
            @error('nim')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <label for="kelas">Kelas</label>
            <input type="text" name="kelas" class="form-control @error('kelas') is-invalid @enderror" value="{{ old('kelas') }}">
            @error('kelas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <label for="ID_Dosen">Dosen Wali</label>
            <select name="ID_Dosen" class="form-control @error('ID_Dosen') is-invalid @enderror">
                <option value="">Pilih Dosen</option>
                @foreach ($dosen as $d)
                    <option value="{{ $d->username }}" {{ old('ID_Dosen') == $d->username ? 'selected' : '' }}>{{ $d->nama }}</option>
                @endforeach
            </select>
            @error('ID_Dosen')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <label for="ID_Perwalian">Perwalian (Opsional)</label>
            <input type="text" name="ID_Perwalian" class="form-control @error('ID_Perwalian') is-invalid @enderror" value="{{ old('ID_Perwalian') }}">
            @error('ID_Perwalian')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Dosen Fields -->
        <div class="form-group" id="dosen-fields" style="display: none;">
            <label for="nama">Nama</label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}">
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <label for="nip">NIP</label>
            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
            @error('nip')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Konselor, Kemahasiswaan, Keasramaan Fields -->
        <div class="form-group" id="nip-fields" style="display: none;">
            <label for="nip">NIP</label>
            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
            @error('nip')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Orang Tua Fields -->
        <div class="form-group" id="orang_tua-fields" style="display: none;">
            <label for="nim">NIM Mahasiswa</label>
            <input type="text" name="nim" class="form-control @error('nim') is-invalid @enderror" value="{{ old('nim') }}">
            @error('nim')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <label for="no_hp">No HP</label>
            <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}">
            @error('no_hp')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
    document.getElementById('role').addEventListener('change', function() {
        document.getElementById('mahasiswa-fields').style.display = this.value === 'mahasiswa' ? 'block' : 'none';
        document.getElementById('dosen-fields').style.display = this.value === 'dosen' ? 'block' : 'none';
        document.getElementById('nip-fields').style.display = ['konselor', 'kemahasiswaan', 'keasramaan'].includes(this.value) ? 'block' : 'none';
        document.getElementById('orang_tua-fields').style.display = this.value === 'orang_tua' ? 'block' : 'none';
    });
</script>
@endsection