@extends('admin.layouts.app')
@section('title', 'Recipient preview')
@section('content')
<div class="dashboard-main-body">
    <h5 class="mb-16">Recipient preview ({{ $total }})</h5>
    <ul>
        @foreach($recipients as $recipient)
            <li>{{ $recipient['email'] }} @if($recipient['name']) — {{ $recipient['name'] }} @endif</li>
        @endforeach
    </ul>
</div>
@endsection
