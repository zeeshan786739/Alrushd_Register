@extends('admin.layouts.app')
@section('title', 'Security')
@section('content')
@include('admin.account.partials.shell', [
    'activeTab' => 'security',
    'shellTitle' => 'Security',
    'shellSubtitle' => 'Keep your admin account secure with a strong password.',
])

<div class="row g-4">
    <div class="col-xl-7">
        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Change password</h2>
                    <p class="acct-panel__desc">Use at least 6 characters. You will stay signed in after updating.</p>
                </div>
            </div>
            <div class="acct-panel__body">
                <form class="needs-validation" novalidate action="{{ route('admin.account.security.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label" for="current_password">Current password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control radius-8 @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="new_password">New password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control radius-8 @error('new_password') is-invalid @enderror" required autocomplete="new-password">
                            @error('new_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="new_password_confirmation">Confirm new password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control radius-8" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="em-settings-footer mt-24 pt-20">
                        <p class="em-settings-footer__hint mb-0">Never share your admin password with teammates — invite them instead.</p>
                        <button class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" type="submit">
                            <iconify-icon icon="solar:shield-check-linear"></iconify-icon> Update password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Security tips</h2>
                    <p class="acct-panel__desc">Best practices for school admin accounts.</p>
                </div>
            </div>
            <div class="acct-tips-list">
                <div class="acct-tip-row">
                    <iconify-icon icon="solar:users-group-two-rounded-linear"></iconify-icon>
                    <div>
                        <strong>Invite teammates</strong>
                        <small>Give staff their own login under Team &amp; Access instead of sharing yours.</small>
                    </div>
                </div>
                <div class="acct-tip-row">
                    <iconify-icon icon="solar:lock-password-linear"></iconify-icon>
                    <div>
                        <strong>Use a unique password</strong>
                        <small>Do not reuse passwords from other services.</small>
                    </div>
                </div>
                <div class="acct-tip-row">
                    <iconify-icon icon="solar:shield-keyhole-linear"></iconify-icon>
                    <div>
                        <strong>Review roles regularly</strong>
                        <small>Remove access when someone leaves your school.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
