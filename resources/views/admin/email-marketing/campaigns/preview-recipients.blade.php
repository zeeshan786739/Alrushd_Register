@extends('admin.layouts.app')
@section('title', 'Recipient preview')
@section('content')
@php $preflight = $preflight ?? null; @endphp
<div class="dashboard-main-body">
    <h5 class="mb-16">Recipient preflight</h5>

    @if($preflight)
    <div class="row g-3 mb-24">
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Selected</div><strong>{{ $preflight['selected'] }}</strong></div></div></div>
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Valid emails</div><strong>{{ $preflight['valid'] }}</strong></div></div></div>
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Duplicates</div><strong>{{ $preflight['duplicates'] }}</strong></div></div></div>
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Unsubscribed</div><strong>{{ $preflight['unsubscribed'] }}</strong></div></div></div>
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Suppressed / bounced</div><strong>{{ $preflight['suppressed'] }}</strong></div></div></div>
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Eligible</div><strong>{{ $preflight['eligible'] }}</strong></div></div></div>
        <div class="col-6 col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Invalid</div><strong>{{ $preflight['invalid'] }}</strong></div></div></div>
    </div>
    @endif

    <h6 class="mb-12">Eligible sample ({{ $total }})</h6>
    <ul>
        @forelse($recipients as $recipient)
            <li>{{ $recipient['email'] }} @if(!empty($recipient['name'])) — {{ $recipient['name'] }} @endif</li>
        @empty
            <li class="text-secondary-light">No eligible recipients.</li>
        @endforelse
    </ul>
</div>
@endsection
