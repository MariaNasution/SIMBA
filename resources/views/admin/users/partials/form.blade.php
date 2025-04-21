<div class="form-group mb-3">
    <label>Nama</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
</div>

<div class="form-group mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
</div>

<div class="form-group mb-3">
    <label>Role</label>
    <select name="role" class="form-control" required>
        <option value="">-- Pilih Role --</option>
        <option value="admin" {{ (old('role', $user->role ?? '') == 'admin') ? 'selected' : '' }}>Admin</option>
        <option value="mahasiswa" {{ (old('role', $user->role ?? '') == 'mahasiswa') ? 'selected' : '' }}>Mahasiswa</option>
        <option value="konselor" {{ (old('role', $user->role ?? '') == 'konselor') ? 'selected' : '' }}>Konselor</option>
    </select>
</div>

<button type="submit" class="btn btn-success">{{ $submit }}</button>
