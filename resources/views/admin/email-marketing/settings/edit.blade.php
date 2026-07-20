@extends('admin.layouts.app')
@section('title', 'Mailbox Settings')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Mailbox Settings',
        'subtitle' => 'Per-organization SMTP/IMAP configuration',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Mailbox Settings']],
    ])
    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <form method="POST" action="{{ route('admin.email.mailbox.settings.update') }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="is_enabled" @checked(old('is_enabled', $settings->is_enabled))><label class="form-check-label" for="is_enabled">Enable mailbox for this organization</label></div></div>
                    <div class="col-md-4"><label class="form-label" for="from_name">From name</label><input id="from_name" name="from_name" class="form-control radius-8" value="{{ old('from_name', $settings->from_name) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="from_email">From email</label><input id="from_email" type="email" name="from_email" class="form-control radius-8" value="{{ old('from_email', $settings->from_email) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="reply_to">Reply-to</label><input id="reply_to" type="email" name="reply_to" class="form-control radius-8" value="{{ old('reply_to', $settings->reply_to) }}"></div>

                    <div class="col-12"><h6 class="mt-12">SMTP</h6></div>
                    <div class="col-md-4"><label class="form-label" for="smtp_host">Host</label><input id="smtp_host" name="smtp_host" class="form-control radius-8" value="{{ old('smtp_host', $settings->smtp_host) }}"></div>
                    <div class="col-md-2"><label class="form-label" for="smtp_port">Port</label><input id="smtp_port" type="number" name="smtp_port" class="form-control radius-8" value="{{ old('smtp_port', $settings->smtp_port) }}"></div>
                    <div class="col-md-2"><label class="form-label" for="smtp_encryption">Encryption</label><select id="smtp_encryption" name="smtp_encryption" class="form-select radius-8"><option value="tls" @selected(old('smtp_encryption',$settings->smtp_encryption)==='tls')>TLS</option><option value="ssl" @selected(old('smtp_encryption',$settings->smtp_encryption)==='ssl')>SSL</option></select></div>
                    <div class="col-md-4"><label class="form-label" for="smtp_username">Username</label><input id="smtp_username" name="smtp_username" class="form-control radius-8" value="{{ old('smtp_username', $settings->smtp_username) }}" autocomplete="off"></div>
                    <div class="col-md-4"><label class="form-label" for="smtp_password">Password</label><input id="smtp_password" type="password" name="smtp_password" class="form-control radius-8" value="" placeholder="{{ $settings->smtp_password ? '•••••••• (leave blank to keep)' : '' }}" autocomplete="new-password"></div>

                    <div class="col-12"><h6 class="mt-12">IMAP</h6></div>
                    <div class="col-md-4"><label class="form-label" for="imap_host">Host</label><input id="imap_host" name="imap_host" class="form-control radius-8" value="{{ old('imap_host', $settings->imap_host) }}"></div>
                    <div class="col-md-2"><label class="form-label" for="imap_port">Port</label><input id="imap_port" type="number" name="imap_port" class="form-control radius-8" value="{{ old('imap_port', $settings->imap_port ?? 993) }}"></div>
                    <div class="col-md-2"><label class="form-label" for="imap_encryption">Encryption</label><select id="imap_encryption" name="imap_encryption" class="form-select radius-8"><option value="ssl" @selected(old('imap_encryption',$settings->imap_encryption??'ssl')==='ssl')>SSL</option><option value="tls" @selected(old('imap_encryption',$settings->imap_encryption)==='tls')>TLS</option></select></div>
                    <div class="col-md-4"><label class="form-label" for="imap_username">Username</label><input id="imap_username" name="imap_username" class="form-control radius-8" value="{{ old('imap_username', $settings->imap_username) }}" autocomplete="off"></div>
                    <div class="col-md-4"><label class="form-label" for="imap_password">Password</label><input id="imap_password" type="password" name="imap_password" class="form-control radius-8" value="" placeholder="{{ $settings->imap_password ? '•••••••• (leave blank to keep)' : '' }}" autocomplete="new-password"></div>
                    <div class="col-md-4"><label class="form-label" for="inbox_folder">Inbox folder</label><input id="inbox_folder" name="inbox_folder" class="form-control radius-8" value="{{ old('inbox_folder', $settings->inbox_folder ?? 'INBOX') }}"></div>

                    <div class="col-12">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="validate_cert" value="1" id="validate_cert" @checked(old('validate_cert', $settings->validate_cert ?? true))><label class="form-check-label" for="validate_cert">Validate TLS certificates</label></div>
                        <div class="form-check mt-8"><input class="form-check-input" type="checkbox" name="tracking_enabled" value="1" id="tracking_enabled" @checked(old('tracking_enabled', $settings->tracking_enabled ?? true))><label class="form-check-label" for="tracking_enabled">Enable campaign tracking</label></div>
                    </div>
                </div>
                <button class="btn btn-primary-600 radius-8 mt-24">Save settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
