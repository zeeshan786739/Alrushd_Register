@extends('layouts.form')

@section('title', 'Al-rushd Online School - Submission Successful')

@section('content')
<div class="ar-success-page">
    <div class="ar-success-card">
        <div class="ar-success-icon">
            <i class="fa fa-check"></i>
        </div>
        <h1 class="ar-success-title">Thank you!</h1>
        <p class="ar-success-text">
            @if ($form?->settings['success_message'] ?? false)
                {{ $form->settings['success_message'] }}
            @else
                Your submission has been received successfully. We will be in touch shortly.
            @endif
        </p>
        <a href="{{ url('/') }}" class="ar-btn ar-btn--primary">Back to Home</a>
    </div>
</div>
@endsection
