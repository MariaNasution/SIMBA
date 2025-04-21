@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">{{ isset($editUser) ? 'Edit User' : 'Tambah User Baru' }}</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Tambah / Edit --}}
    <form action="{{ isset($editUser) ? route('users.update', $editUser->id) : route('users.store') }}" method="POST">
        @csrf
        @if(isset($editUser))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                value="{{ old('username', $editUser->username ?? '') }}"
                required
            >
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                value="{{ old('email', $editUser->email ?? '') }}"
                required
            >
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password {{ isset($editUser) ? '(Kosongkan jika tidak diganti)' : '' }}</label>
            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                {{ isset($editUser) ? '' : 'required' }}
            >
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-control" id="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="admin" {{ old('role', $editUser->role ?? '') == 'admin' ? 'selected' : '' }}>Dosen</option>
                <option value="mahasiswa" {{ old('role', $editUser->role ?? '') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="konselor" {{ old('role', $editUser->role ?? '') == 'konselor' ? 'selected' : '' }}>Konselor</option>
                <option value="konselor" {{ old('role', $editUser->role ?? '') == 'kemahasiswaan' ? 'selected' : '' }}>Kemahasiswaan</option>
                <option value="konselor" {{ old('role', $editUser->role ?? '') == 'keasramaan' ? 'selected' : '' }}>Keasramaan</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            @if(isset($editUser))
                <a href="{{ route('admin_add-user') }}" class="btn btn-secondary">← Batal Edit</a>
            @endif
            <button type="submit" class="btn btn-success">{{ isset($editUser) ? 'Update' : 'Simpan' }}</button>
        </div>
    </form>

    <hr class="my-5">

    <h4 class="mb-3">Daftar User</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>
                        <a href="{{ route('admin_add-user', ['edit' => $user->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('styles')
<style>
    .form-label {
        font-weight: 600;
    }
</style>
@endsection
