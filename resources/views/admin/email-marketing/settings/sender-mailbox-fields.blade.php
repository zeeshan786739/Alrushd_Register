@php($field = fn ($name, $fallback = '') => old($name, $sender?->{$name} ?? $fallback))
<div class="row g-3">
    <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_name">Display name</label><input id="{{ $prefix }}_name" name="name" class="form-control" value="{{ $field('name') }}" placeholder="Admissions Team"></div>
    <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_email">Verified email</label><input id="{{ $prefix }}_email" type="email" name="email" class="form-control" value="{{ $field('email') }}" required placeholder="admissions@example.com"></div>
    <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_reply">Reply-to</label><input id="{{ $prefix }}_reply" type="email" name="reply_to" class="form-control" value="{{ $field('reply_to') }}" placeholder="Optional"></div>
</div>
<div class="d-flex flex-wrap gap-20 my-16">
    <label class="form-check"><input class="form-check-input" type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $sender?->is_verified ?? false))> <span class="form-check-label">Verified in SendGrid</span></label>
    <label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $sender?->is_active ?? true))> <span class="form-check-label">Active</span></label>
    <label class="form-check"><input class="form-check-input" type="checkbox" name="is_default" value="1" @checked(old('is_default', $sender?->is_default ?? false))> <span class="form-check-label">Default sender</span></label>
</div>
<div class="border-top pt-16">
    <h4 class="text-base mb-4">Inbox connection <span class="text-secondary-light fw-normal">(optional)</span></h4>
    <p class="text-sm text-secondary-light">Add IMAP details only if this address should have an inbox inside the application.</p>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_host">IMAP host</label><input id="{{ $prefix }}_host" name="imap_host" class="form-control" value="{{ $field('imap_host') }}" placeholder="imap.hostinger.com"></div>
        <div class="col-md-2"><label class="form-label" for="{{ $prefix }}_port">Port</label><input id="{{ $prefix }}_port" type="number" name="imap_port" class="form-control" value="{{ $field('imap_port', 993) }}"></div>
        <div class="col-md-2"><label class="form-label" for="{{ $prefix }}_encryption">Encryption</label><select id="{{ $prefix }}_encryption" name="imap_encryption" class="form-select"><option value="ssl" @selected($field('imap_encryption', 'ssl') === 'ssl')>SSL</option><option value="tls" @selected($field('imap_encryption') === 'tls')>TLS</option><option value="none" @selected($field('imap_encryption') === 'none')>None</option></select></div>
        <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_username">Username</label><input id="{{ $prefix }}_username" name="imap_username" class="form-control" value="{{ $field('imap_username') }}" autocomplete="off"></div>
        <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_password">Password</label><input id="{{ $prefix }}_password" type="password" name="imap_password" class="form-control" autocomplete="new-password" placeholder="{{ $sender?->imap_password ? 'Leave blank to keep saved password' : '' }}"></div>
        <div class="col-md-4"><label class="form-label" for="{{ $prefix }}_folder">Inbox folder</label><input id="{{ $prefix }}_folder" name="inbox_folder" class="form-control" value="{{ $field('inbox_folder', 'INBOX') }}"></div>
        <div class="col-md-4 d-flex align-items-end pb-10"><label class="form-check"><input class="form-check-input" type="checkbox" name="validate_cert" value="1" @checked(old('validate_cert', $sender?->validate_cert ?? true))> <span class="form-check-label">Validate TLS certificate</span></label></div>
    </div>
</div>
