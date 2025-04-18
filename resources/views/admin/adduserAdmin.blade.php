@extends('layouts.app')

@section('content')
  <div class="container-fluid p-4">
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
@endsection