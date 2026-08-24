@extends('admin.layouts.app')
@section('title', 'Public Website')
@section('content')
@include('admin.account.partials.shell', [
    'activeTab' => 'website',
    'shellTitle' => 'Public Website',
    'shellSubtitle' => 'Your school\'s live site URL — separate from the Enrolliq admin panel.',
])

<div class="row g-4">
    <div class="col-xxl-6">
        <div class="acct-panel mb-24">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Your website link</h2>
                    <p class="acct-panel__desc">Share this URL with families. Content comes from Website CMS; forms from Form Center.</p>
                </div>
            </div>
            <div class="acct-panel__body">
                <div class="acct-url-box mb-16">
                    <input type="text" class="form-control radius-8" readonly value="{{ $publicUrl }}" id="publicWebsiteUrl">
                    <button type="button" class="btn btn-outline-primary radius-8 fc-btn" data-copy-target="publicWebsiteUrl">
                        <iconify-icon icon="solar:copy-linear"></iconify-icon> Copy
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-8">
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-primary-600 radius-8 fc-btn">
                        <iconify-icon icon="solar:globus-linear"></iconify-icon> Open website
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary radius-8 fc-btn">
                        <iconify-icon icon="solar:pallete-2-linear"></iconify-icon> Edit in Website CMS
                    </a>
                </div>
                <p class="text-sm acct-muted mt-16 mb-0">
                    Admin login stays at <code>{{ url('/admin/login') }}</code> — customers never access the platform super admin panel.
                </p>
            </div>
        </div>
    </div>

    <div class="col-xxl-6">
        <div class="acct-panel">
            <div class="acct-panel__head">
                <div>
                    <h2 class="acct-panel__title">Connect custom domain</h2>
                    <p class="acct-panel__desc">Point your own domain (e.g. www.yourschool.com) to your Enrolliq site.</p>
                </div>
            </div>
            <div class="acct-panel__body">
                @if(session('success'))
                    <div class="alert alert-success radius-8">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger radius-8">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.account.website.update') }}" class="mb-20">
                    @csrf
                    @method('PUT')
                    <label class="form-label fw-semibold">Custom domain</label>
                    <input type="text" name="custom_domain" class="form-control radius-8 @error('custom_domain') is-invalid @enderror"
                           value="{{ old('custom_domain', $organization->custom_domain) }}" placeholder="www.yourschool.com">
                    @error('custom_domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-primary-600 radius-8 fc-btn mt-12">Save domain</button>
                </form>

                @if($organization->custom_domain)
                <div class="acct-dns-steps">
                    <p class="fw-semibold mb-8">DNS verification</p>
                    <ol class="acct-muted text-sm mb-16">
                        <li>Add a TXT record on <code>{{ $verificationHost }}</code></li>
                        <li>Value: <code>{{ $verificationRecord }}</code></li>
                        <li>Point your domain CNAME to this app (or use your host's custom domain mapping)</li>
                    </ol>
                    @if($organization->hasVerifiedCustomDomain())
                        <span class="badge bg-success-focus text-success-main radius-8 px-12 py-6">Verified</span>
                    @else
                        <form method="POST" action="{{ route('admin.account.website.verify') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success radius-8 fc-btn">Verify DNS</button>
                        </form>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-copy-target]').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.copyTarget);
        if (!input) return;
        input.select();
        navigator.clipboard?.writeText(input.value);
        btn.textContent = 'Copied';
        setTimeout(() => { btn.innerHTML = '<iconify-icon icon="solar:copy-linear"></iconify-icon> Copy'; }, 1500);
    });
});
</script>
@endpush
@endsection
