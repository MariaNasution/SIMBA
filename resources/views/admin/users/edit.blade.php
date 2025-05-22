@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit User</h1>
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="username">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">Password (Kosongkan jika tidak ingin mengubah)</label>
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
            <label for="role">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-control @error('role') is-invalid @enderror" id="role" required>
                <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="dosen" {{ old('role', $user->role) == 'dosen' ? 'selected' : '' }}>Dosen</option>
                <option value="konselor" {{ old('role', $user->role) == 'konselor' ? 'selected' : '' }}>Konselor</option>
                <option value="kemahasiswaan" {{ old('role', $user->role) == 'kemahasiswaan' ? 'selected' : '' }}>Kemahasiswaan</option>
                <option value="keasramaan" {{ old('role', $user->role) == 'keasramaan' ? 'selected' : '' }}>Keasramaan</option>
                <option value="orang_tua" {{ old('role', $user->role) == 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Mahasiswa Fields -->
        <div id="mahasiswa-fields" style="display: {{ old('role', $user->role) == 'mahasiswa' ? 'block' : 'none' }};">
            <div class="form-group">
                <label for="nama">Nama <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $user->mahasiswa?->nama) }}">
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="nim">NIM <span class="text-danger">*</span></label>
                <input type="text" name="nim" class="form-control @error('nim') is-invalid @enderror" value="{{ old('nim', $user->mahasiswa?->nim) }}" maxlength="8">
                @error('nim')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="kelas">Kelas <span class="text-danger">*</span></label>
                <input type="text" name="kelas" class="form-control @error('kelas') is-invalid @enderror" value="{{ old('kelas', $user->mahasiswa?->kelas) }}" maxlength="10">
                @error('kelas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="anak_wali">Dosen Wali (NIP) <span class="text-danger">*</span></label>
                <select name="anak_wali" class="form-control @error('anak_wali') is-invalid @enderror">
                    <option value="">Pilih Dosen</option>
                    @foreach ($dosen as $d)
                        <option value="{{ $d->nip }}" {{ old('anak_wali', $user->anak_wali) == $d->nip ? 'selected' : '' }}>{{ $d->nama }} ({{ $d->nip }})</option>
                    @endforeach
                </select>
                @error('anak_wali')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="ID_Perwalian">Perwalian (Opsional)</label>
                <input type="text" name="ID_Perwalian" class="form-control @error('ID_Perwalian') is-invalid @enderror" value="{{ old('ID_Perwalian', $user->mahasiswa?->ID_Perwalian) }}">
                @error('ID_Perwalian')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <!-- Dosen Fields -->
        <div id="dosen-fields" style="display: {{ old('role', $user->role) == 'dosen' ? 'block' : 'none' }};">
            <div class="form-group">
                <label for="nama">Nama <span class="text-danger">*</span></label>
                <input type="text" name="nama-dosen" class="form-control @error('nama-dosen') is-invalid @enderror" value="{{ old('nama', $user->dosen?->nama) }}">
                @error('nama-dosen')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="nip">NIP <span class="text-danger">*</span></label>
                <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $user->dosen?->nip) }}" maxlength="20">
                @error('nip')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <!-- Konselor, Kemahasiswaan, Keasramaan Fields -->
        <div id="nip-fields" style="display: {{ in_array(old('role', $user->role), ['konselor', 'kemahasiswaan', 'keasramaan']) ? 'block' : 'none' }};">
            <div class="form-group">
                <label for="nip">NIP (Opsional)</label>
                <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $user->konselor?->nip ?? $user->kemahasiswaan?->nip ?? $user->keasramaan?->nip) }}" maxlength="20">
                @error('nip')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <!-- Orang Tua Fields -->
        <div id="orang_tua-fields" style="display: {{ old('role', $user->role) == 'orang_tua' ? 'block' : 'none' }};">
            <div class="form-group">
                <label for="nim">NIM Mahasiswa (Opsional)</label>
                <input type="text" name="nim-mahasiswa" class="form-control @error('nim-mahasiswa') is-invalid @enderror" value="{{ old('nim', $user->orangTua?->nim) }}" maxlength="8">
                @error('nim-mahasiswa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="no_hp">No HP <span class="text-danger">*</span></label>
                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $user->orangTua?->no_hp) }}" maxlength="15">
                @error('no_hp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
    document.getElementById('role').addEventListener('change', function() {
        const mahasiswaFields = document.getElementById('mahasiswa-fields');
        const dosenFields = document.getElementById('dosen-fields');
        const nipFields = document.getElementById('nip-fields');
        const orangTuaFields = document.getElementById('orang_tua-fields');

        // Reset required attributes
        document.querySelectorAll('#mahasiswa-fields input, #mahasiswa-fields select').forEach(el => el.removeAttribute('required'));
        document.querySelectorAll('#dosen-fields input').forEach(el => el.removeAttribute('required'));
        document.querySelectorAll('#orang_tua-fields input').forEach(el => el.removeAttribute('required'));

        // Show/hide fields
        mahasiswaFields.style.display = this.value === 'mahasiswa' ? 'block' : 'none';
        dosenFields.style.display = this.value === 'dosen' ? 'block' : 'none';
        nipFields.style.display = ['konselor', 'kemahasiswaan', 'keasramaan'].includes(this.value) ? 'block' : 'none';
        orangTuaFields.style.display = this.value === 'orang_tua' ? 'block' : 'none';

        // Set required attributes based on role
        if (this.value === 'mahasiswa') {
            document.querySelector('#mahasiswa-fields [name="nama"]').setAttribute('required', 'required');
            document.querySelector('#mahasiswa-fields [name="nim"]').setAttribute('required', 'required');
            document.querySelector('#mahasiswa-fields [name="kelas"]').setAttribute('required', 'required');
            document.querySelector('#mahasiswa-fields [name="anak_wali"]').setAttribute('required', 'required');
        } else if (this.value === 'dosen') {
            document.querySelector('#dosen-fields [name="nama-dosen"]').setAttribute('required', 'required');
            document.querySelector('#dosen-fields [name="nip"]').setAttribute('required', 'required');
        } else if (this.value === 'orang_tua') {
            document.querySelector('#orang_tua-fields [name="no_hp"]').setAttribute('required', 'required');
        }
    });

    document.getElementById('role').dispatchEvent(new Event('change'));
</script>
@endsection