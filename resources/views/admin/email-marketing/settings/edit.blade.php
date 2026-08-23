@extends('admin.layouts.app')
@section('title', 'Mailbox Settings')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'settings',
    'shellTitle' => 'Mailbox settings',
    'shellSubtitle' => 'Configure sender identity, SendGrid tracking, and optional SMTP/IMAP fallback.',
])

<div class="em-panel em-settings-panel">
    <form method="POST" action="{{ route('admin.email.mailbox.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="em-form-block mb-24">
            <h3 class="em-form-block__title">Sender identity</h3>
            <p class="em-form-block__desc">How recipients see your school in the inbox — name and addresses used on outbound mail.</p>
            <div class="form-check mb-20">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="is_enabled" @checked(old('is_enabled', $settings->is_enabled))>
                <label class="form-check-label" for="is_enabled">Enable mailbox for this school</label>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label" for="from_name">From name</label>
                    <input id="from_name" name="from_name" class="form-control radius-8" value="{{ old('from_name', $settings->from_name) }}" placeholder="e.g. Al-Rushd Admissions">
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label" for="from_email">From email</label>
                    <input id="from_email" type="email" name="from_email" class="form-control radius-8" value="{{ old('from_email', $settings->from_email) }}" placeholder="hello@yourschool.com">
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label" for="reply_to">Reply-to</label>
                    <input id="reply_to" type="email" name="reply_to" class="form-control radius-8" value="{{ old('reply_to', $settings->reply_to) }}" placeholder="replies@yourschool.com">
                    <div class="form-text">Optional. Used when inbound reply routing is not enabled.</div>
                </div>
            </div>
        </div>

        <div class="em-form-block mb-24">
            <h3 class="em-form-block__title">SendGrid</h3>
            <p class="em-form-block__desc">Delivery provider status for campaigns and transactional sends.</p>
            <div class="alert {{ $sendGridConfigured ? 'alert-success' : 'alert-warning' }} radius-8 mb-0">
                <strong>{{ $sendGridConfigured ? 'Configured' : 'Not configured' }}</strong>
                <div class="text-sm mt-4">{{ $providerLabel }}</div>
                <div class="text-sm mt-4">API keys stay in server environment variables — never stored here.</div>
            </div>
        </div>

        <div class="em-form-block mb-24">
            <h3 class="em-form-block__title">Inbound replies</h3>
            <p class="em-form-block__desc">Route replies from campaigns back into your school inbox.</p>
            <div class="row g-4 align-items-end">
                <div class="col-12 col-lg-7">
                    <label class="form-label" for="inbound_domain">Inbound domain</label>
                    <input id="inbound_domain" name="inbound_domain" class="form-control radius-8" value="{{ old('inbound_domain', $settings->inbound_domain) }}" placeholder="e.g. inbound.example.com">
                </div>
                <div class="col-12 col-lg-5">
                    <div class="form-check mb-0 py-8">
                        <input class="form-check-input" type="checkbox" name="inbound_enabled" value="1" id="inbound_enabled" @checked(old('inbound_enabled', $settings->inbound_enabled))>
                        <label class="form-check-label" for="inbound_enabled">Enable inbound reply routing</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="em-form-block mb-24">
            <h3 class="em-form-block__title">Tracking</h3>
            <p class="em-form-block__desc">Open and click analytics for campaigns and provider-level events.</p>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="tracking_enabled" value="1" id="tracking_enabled" @checked(old('tracking_enabled', $settings->tracking_enabled ?? true))><label class="form-check-label" for="tracking_enabled">Campaign open/click tracking</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="open_tracking" value="1" id="open_tracking" @checked(old('open_tracking', $settings->open_tracking ?? true))><label class="form-check-label" for="open_tracking">Provider open tracking</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="click_tracking" value="1" id="click_tracking" @checked(old('click_tracking', $settings->click_tracking ?? true))><label class="form-check-label" for="click_tracking">Provider click tracking</label></div>
        </div>

        <div class="em-form-block mb-24">
            <h3 class="em-form-block__title">Marketing unsubscribe</h3>
            <p class="em-form-block__desc">SendGrid ASM group for one-click unsubscribe on marketing mail.</p>
            <label class="form-label" for="sendgrid_asm_group_id">SendGrid unsubscribe group ID (ASM)</label>
            <input id="sendgrid_asm_group_id" type="number" min="1" name="sendgrid_asm_group_id" class="form-control radius-8" style="max-width:320px" value="{{ old('sendgrid_asm_group_id', $settings->sendgrid_asm_group_id) }}" placeholder="e.g. 12345">
        </div>

        <details class="em-form-block mb-24">
            <summary class="em-form-block__title mb-0" style="cursor:pointer">SMTP / IMAP fallback (optional)</summary>
            <p class="em-form-block__desc mt-12 mb-0">Legacy delivery and optional IMAP sync when SendGrid is unavailable.</p>
            <div class="row g-4 mt-16">
                <div class="col-12"><strong class="text-sm">SMTP</strong></div>
                <div class="col-md-4"><label class="form-label" for="smtp_host">Host</label><input id="smtp_host" name="smtp_host" class="form-control radius-8" value="{{ old('smtp_host', $settings->smtp_host) }}"></div>
                <div class="col-md-2"><label class="form-label" for="smtp_port">Port</label><input id="smtp_port" type="number" name="smtp_port" class="form-control radius-8" value="{{ old('smtp_port', $settings->smtp_port) }}"></div>
                <div class="col-md-2"><label class="form-label" for="smtp_encryption">Encryption</label><select id="smtp_encryption" name="smtp_encryption" class="form-select radius-8"><option value="tls" @selected(old('smtp_encryption', $settings->smtp_encryption) === 'tls')>TLS</option><option value="ssl" @selected(old('smtp_encryption', $settings->smtp_encryption) === 'ssl')>SSL</option></select></div>
                <div class="col-md-4"><label class="form-label" for="smtp_username">Username</label><input id="smtp_username" name="smtp_username" class="form-control radius-8" value="{{ old('smtp_username', $settings->smtp_username) }}" autocomplete="off"></div>
                <div class="col-md-4"><label class="form-label" for="smtp_password">Password</label><input id="smtp_password" type="password" name="smtp_password" class="form-control radius-8" value="" placeholder="{{ $settings->smtp_password ? '•••••••• (leave blank to keep)' : '' }}" autocomplete="new-password"></div>
                <div class="col-12 mt-8"><strong class="text-sm">IMAP</strong></div>
                <div class="col-md-4"><label class="form-label" for="imap_host">Host</label><input id="imap_host" name="imap_host" class="form-control radius-8" value="{{ old('imap_host', $settings->imap_host) }}"></div>
                <div class="col-md-2"><label class="form-label" for="imap_port">Port</label><input id="imap_port" type="number" name="imap_port" class="form-control radius-8" value="{{ old('imap_port', $settings->imap_port ?? 993) }}"></div>
                <div class="col-md-2"><label class="form-label" for="imap_encryption">Encryption</label><select id="imap_encryption" name="imap_encryption" class="form-select radius-8"><option value="ssl" @selected(old('imap_encryption', $settings->imap_encryption ?? 'ssl') === 'ssl')>SSL</option><option value="tls" @selected(old('imap_encryption', $settings->imap_encryption) === 'tls')>TLS</option></select></div>
                <div class="col-md-4"><label class="form-label" for="imap_username">Username</label><input id="imap_username" name="imap_username" class="form-control radius-8" value="{{ old('imap_username', $settings->imap_username) }}" autocomplete="off"></div>
                <div class="col-md-4"><label class="form-label" for="imap_password">Password</label><input id="imap_password" type="password" name="imap_password" class="form-control radius-8" value="" placeholder="{{ $settings->imap_password ? '•••••••• (leave blank to keep)' : '' }}" autocomplete="new-password"></div>
                <div class="col-md-4"><label class="form-label" for="inbox_folder">Inbox folder</label><input id="inbox_folder" name="inbox_folder" class="form-control radius-8" value="{{ old('inbox_folder', $settings->inbox_folder ?? 'INBOX') }}"></div>
                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="validate_cert" value="1" id="validate_cert" @checked(old('validate_cert', $settings->validate_cert ?? true))><label class="form-check-label" for="validate_cert">Validate TLS certificates</label></div></div>
            </div>
        </details>

        <div class="em-form-actions">
            <button class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon> Save settings
            </button>
        </div>
    </form>
</div>
@endsection
