@extends('layouts.app')

@section('content')
<h2>Tambah User</h2>

<form action="{{ route('admin.users.store') }}" method="POST">
    @csrf

    @include('admin.users.partials.form', ['submit' => 'Tambah'])
</form>
@endsection
