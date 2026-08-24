@extends('admin.layouts.app')
@section('title', 'Mailbox Settings')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'settings',
    'shellTitle' => 'Mailbox settings',
    'shellSubtitle' => 'Configure sender identity, SendGrid tracking, and optional SMTP/IMAP fallback.',
])

<div class="em-panel em-settings-panel">
    <form method="POST" action="{{ route('admin.email.mailbox.settings.update') }}" class="em-settings-form">
        @csrf
        @method('PUT')

        <div class="em-settings-stack">
            {{-- Sender identity --}}
            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:user-id-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">Sender identity</h3>
                        <p class="em-form-block__desc">How recipients see your school in the inbox.</p>
                    </div>
                </div>

                <label class="em-toggle-row em-toggle-row--featured">
                    <span class="em-toggle-row__text">
                        <strong>Enable mailbox for this school</strong>
                        <small>Turn on outbound email and inbox features for this workspace.</small>
                    </span>
                    <input class="em-toggle-row__input" type="checkbox" name="is_enabled" value="1" id="is_enabled" @checked(old('is_enabled', $settings->is_enabled))>
                    <span class="em-toggle-row__switch" aria-hidden="true"></span>
                </label>

                <div class="em-form-block__fields row g-4">
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
                        <div class="form-text">Optional when inbound reply routing is enabled.</div>
                    </div>
                </div>
            </section>

            {{-- SendGrid --}}
            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:cloud-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">SendGrid</h3>
                        <p class="em-form-block__desc">Delivery provider status for campaigns and transactional sends.</p>
                    </div>
                </div>
                <div class="em-settings-alert {{ $sendGridConfigured ? 'em-settings-alert--success' : 'em-settings-alert--warning' }}">
                    <span class="em-settings-alert__icon">
                        <iconify-icon icon="{{ $sendGridConfigured ? 'solar:check-circle-linear' : 'solar:info-circle-linear' }}"></iconify-icon>
                    </span>
                    <div class="em-settings-alert__body">
                        <strong>{{ $sendGridConfigured ? 'Configured' : 'Not configured' }}</strong>
                        <p>{{ $providerLabel }}</p>
                        <p class="mb-0">API keys stay in server environment variables — never stored here.</p>
                    </div>
                </div>
            </section>

            {{-- Inbound replies --}}
            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:inbox-in-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">Inbound replies</h3>
                        <p class="em-form-block__desc">Route replies from campaigns back into your school inbox.</p>
                    </div>
                </div>

                <div class="em-form-block__fields">
                    <label class="form-label" for="inbound_domain">Inbound domain</label>
                    <input id="inbound_domain" name="inbound_domain" class="form-control radius-8 em-settings-input-narrow" value="{{ old('inbound_domain', $settings->inbound_domain) }}" placeholder="e.g. inbound.example.com">
                    <div class="form-text">DNS for this domain must point to your inbound provider.</div>
                </div>

                <label class="em-toggle-row">
                    <span class="em-toggle-row__text">
                        <strong>Enable inbound reply routing</strong>
                        <small>Replies to campaigns are captured in your marketing inbox.</small>
                    </span>
                    <input class="em-toggle-row__input" type="checkbox" name="inbound_enabled" value="1" id="inbound_enabled" @checked(old('inbound_enabled', $settings->inbound_enabled))>
                    <span class="em-toggle-row__switch" aria-hidden="true"></span>
                </label>
            </section>

            {{-- Tracking --}}
            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:chart-2-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">Tracking</h3>
                        <p class="em-form-block__desc">Open and click analytics for campaigns and provider events.</p>
                    </div>
                </div>

                <div class="em-toggle-group">
                    <label class="em-toggle-row">
                        <span class="em-toggle-row__text">
                            <strong>Campaign open/click tracking</strong>
                            <small>Measure engagement on emails sent from this module.</small>
                        </span>
                        <input class="em-toggle-row__input" type="checkbox" name="tracking_enabled" value="1" id="tracking_enabled" @checked(old('tracking_enabled', $settings->tracking_enabled ?? true))>
                        <span class="em-toggle-row__switch" aria-hidden="true"></span>
                    </label>
                    <label class="em-toggle-row">
                        <span class="em-toggle-row__text">
                            <strong>Provider open tracking</strong>
                            <small>SendGrid pixel-based open detection.</small>
                        </span>
                        <input class="em-toggle-row__input" type="checkbox" name="open_tracking" value="1" id="open_tracking" @checked(old('open_tracking', $settings->open_tracking ?? true))>
                        <span class="em-toggle-row__switch" aria-hidden="true"></span>
                    </label>
                    <label class="em-toggle-row">
                        <span class="em-toggle-row__text">
                            <strong>Provider click tracking</strong>
                            <small>Rewrite links for click analytics via SendGrid.</small>
                        </span>
                        <input class="em-toggle-row__input" type="checkbox" name="click_tracking" value="1" id="click_tracking" @checked(old('click_tracking', $settings->click_tracking ?? true))>
                        <span class="em-toggle-row__switch" aria-hidden="true"></span>
                    </label>
                </div>
            </section>

            {{-- Marketing unsubscribe --}}
            <section class="em-form-block">
                <div class="em-form-block__head">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:shield-check-linear"></iconify-icon></span>
                    <div>
                        <h3 class="em-form-block__title">Marketing unsubscribe</h3>
                        <p class="em-form-block__desc">SendGrid ASM group for one-click unsubscribe on marketing mail.</p>
                    </div>
                </div>
                <div class="em-form-block__fields">
                    <label class="form-label" for="sendgrid_asm_group_id">SendGrid unsubscribe group ID (ASM)</label>
                    <input id="sendgrid_asm_group_id" type="number" min="1" name="sendgrid_asm_group_id" class="form-control radius-8 em-settings-input-narrow" value="{{ old('sendgrid_asm_group_id', $settings->sendgrid_asm_group_id) }}" placeholder="e.g. 12345">
                </div>
            </section>

            {{-- SMTP / IMAP fallback --}}
            <details class="em-form-block em-form-block--collapsible">
                <summary class="em-form-block__summary">
                    <span class="em-form-block__head em-form-block__head--inline">
                        <span class="em-form-block__icon"><iconify-icon icon="solar:server-linear"></iconify-icon></span>
                        <span>
                            <span class="em-form-block__title">SMTP / IMAP fallback</span>
                            <span class="em-form-block__desc mb-0">Optional legacy delivery and IMAP sync.</span>
                        </span>
                    </span>
                    <iconify-icon icon="solar:alt-arrow-down-linear" class="em-form-block__chevron"></iconify-icon>
                </summary>

                <div class="em-form-block__collapse-body">
                    <div class="em-settings-subsection">
                        <h4 class="em-settings-subsection__title">SMTP</h4>
                        <div class="row g-4">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="smtp_host">Host</label>
                                <input id="smtp_host" name="smtp_host" class="form-control radius-8" value="{{ old('smtp_host', $settings->smtp_host) }}">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label" for="smtp_port">Port</label>
                                <input id="smtp_port" type="number" name="smtp_port" class="form-control radius-8" value="{{ old('smtp_port', $settings->smtp_port) }}">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label" for="smtp_encryption">Encryption</label>
                                <select id="smtp_encryption" name="smtp_encryption" class="form-select radius-8">
                                    <option value="tls" @selected(old('smtp_encryption', $settings->smtp_encryption) === 'tls')>TLS</option>
                                    <option value="ssl" @selected(old('smtp_encryption', $settings->smtp_encryption) === 'ssl')>SSL</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="smtp_username">Username</label>
                                <input id="smtp_username" name="smtp_username" class="form-control radius-8" value="{{ old('smtp_username', $settings->smtp_username) }}" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="smtp_password">Password</label>
                                <input id="smtp_password" type="password" name="smtp_password" class="form-control radius-8" value="" placeholder="{{ $settings->smtp_password ? '•••••••• (leave blank to keep)' : '' }}" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="em-settings-subsection">
                        <h4 class="em-settings-subsection__title">IMAP</h4>
                        <div class="row g-4">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="imap_host">Host</label>
                                <input id="imap_host" name="imap_host" class="form-control radius-8" value="{{ old('imap_host', $settings->imap_host) }}">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label" for="imap_port">Port</label>
                                <input id="imap_port" type="number" name="imap_port" class="form-control radius-8" value="{{ old('imap_port', $settings->imap_port ?? 993) }}">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label" for="imap_encryption">Encryption</label>
                                <select id="imap_encryption" name="imap_encryption" class="form-select radius-8">
                                    <option value="ssl" @selected(old('imap_encryption', $settings->imap_encryption ?? 'ssl') === 'ssl')>SSL</option>
                                    <option value="tls" @selected(old('imap_encryption', $settings->imap_encryption) === 'tls')>TLS</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="imap_username">Username</label>
                                <input id="imap_username" name="imap_username" class="form-control radius-8" value="{{ old('imap_username', $settings->imap_username) }}" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="imap_password">Password</label>
                                <input id="imap_password" type="password" name="imap_password" class="form-control radius-8" value="" placeholder="{{ $settings->imap_password ? '•••••••• (leave blank to keep)' : '' }}" autocomplete="new-password">
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="inbox_folder">Inbox folder</label>
                                <input id="inbox_folder" name="inbox_folder" class="form-control radius-8" value="{{ old('inbox_folder', $settings->inbox_folder ?? 'INBOX') }}">
                            </div>
                        </div>
                    </div>

                    <label class="em-toggle-row">
                        <span class="em-toggle-row__text">
                            <strong>Validate TLS certificates</strong>
                            <small>Recommended for production mail servers.</small>
                        </span>
                        <input class="em-toggle-row__input" type="checkbox" name="validate_cert" value="1" id="validate_cert" @checked(old('validate_cert', $settings->validate_cert ?? true))>
                        <span class="em-toggle-row__switch" aria-hidden="true"></span>
                    </label>
                </div>
            </details>
        </div>

        <div class="em-settings-footer">
            <p class="em-settings-footer__hint">Changes apply to this school only. Delivery keys remain on the server.</p>
            <button class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn" type="submit">
                <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                Save settings
            </button>
        </div>
    </form>
</div>
@endsection
