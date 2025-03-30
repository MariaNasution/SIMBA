@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Send SMS</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ url('/send-sms') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="to">Recipient Number</label>
            <input type="text" name="to" id="to" class="form-control" placeholder="+1234567890" required>
        </div>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Send SMS</button>
    </form>
</div>
@endsection
