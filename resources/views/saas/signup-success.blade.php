@extends('saas.layout')

@php $saasName = \App\Models\PlatformSetting::get('platform_name', config('saas.name')); @endphp

@section('title', 'Welcome to ' . $saasName)

@section('page_css')
        .success-wrap { max-width: 600px; margin: 0 auto; padding: 100px 24px; text-align: center; }
        .big-icon { font-size: 72px; margin-bottom: 20px; }
        .next-steps { text-align: left; background: var(--bg-soft); border: 1px solid var(--line); border-radius: 16px; padding: 28px 32px; margin: 32px 0; }
        .next-steps li { margin-bottom: 12px; font-size: 15px; color: var(--ink-soft); }
@endsection

@section('content')
<div class="success-wrap">
    @if(($checkoutCancelled ?? false))
        <div class="big-icon">🕐</div>
        <h1 class="headline">Payment postponed — your trial is still on!</h1>
        <p class="lede" style="margin: 0 auto 24px;">
            No worries — {{ $organization?->name ?? 'your school' }} is created and your free trial is active.
            You can add payment any time from the Billing page inside your admin panel.
        </p>
    @elseif(($paid ?? false))
        <div class="big-icon">🎉</div>
        <h1 class="headline">You're all set{{ $organization ? ', ' . $organization->name : '' }}!</h1>
        <p class="lede" style="margin: 0 auto 24px;">
            Your subscription is active. Welcome aboard — let's fill some classrooms.
        </p>
    @else
        <div class="big-icon">🚀</div>
        <h1 class="headline">Welcome to {{ $saasName }}{{ $organization ? ', ' . $organization->name : '' }}!</h1>
        <p class="lede" style="margin: 0 auto 24px;">
            Your school workspace is ready and your free trial has started.
        </p>
    @endif

    <div class="next-steps">
        <strong style="display:block; margin-bottom: 14px; font-size: 16px;">Next steps</strong>
        <ol style="padding-left: 20px;">
            <li>Log in to your admin panel with the email and password you just created.</li>
            <li>Build your first admission or enquiry form in the Form Center.</li>
            <li>Invite your team and connect your lead channels.</li>
        </ol>
    </div>

    <a href="{{ route('admin.login') }}" class="btn btn-primary btn-lg">Go to My Admin Panel →</a>
</div>
@endsection
