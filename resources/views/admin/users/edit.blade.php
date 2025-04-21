@extends('layouts.app')

@section('content')
<h2>Edit User</h2>

<form action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    @include('admin.users.partials.form', ['submit' => 'Update'])
</form>
@endsection
