@php
    $c = $campaign;
    $filters = is_object($c) ? ($c->recipient_filters ?? []) : [];
    $currentSource = old('recipient_source', is_object($c) ? ($c->recipient_source ?? 'leads') : 'leads');
    $leadStatuses = $leadStatuses ?? \App\Enums\LeadStatus::options();
    $leadPriorities = $leadPriorities ?? \App\Enums\LeadPriority::options();
    $isEdit = is_object($c);
@endphp

@if($errors->any())
<div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mb-20">
    <strong class="d-block mb-4">Please fix the following:</strong>
    <ul class="mb-0 ps-20">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="em-wizard" data-campaign-wizard>
    <nav class="em-wizard__steps" aria-label="Campaign builder progress">
        <button type="button" class="em-wizard__step is-active" data-wizard-goto="1">
            <span class="em-wizard__step-num">1</span>
            <span class="em-wizard__step-text"><strong>Details</strong><small>Name & sender</small></span>
        </button>
        <button type="button" class="em-wizard__step" data-wizard-goto="2">
            <span class="em-wizard__step-num">2</span>
            <span class="em-wizard__step-text"><strong>Audience</strong><small>Who receives it</small></span>
        </button>
        <button type="button" class="em-wizard__step" data-wizard-goto="3">
            <span class="em-wizard__step-num">3</span>
            <span class="em-wizard__step-text"><strong>Design</strong><small>Email content</small></span>
        </button>
        <button type="button" class="em-wizard__step" data-wizard-goto="4">
            <span class="em-wizard__step-num">4</span>
            <span class="em-wizard__step-text"><strong>Review</strong><small>Confirm & send</small></span>
        </button>
    </nav>

    {{-- Step 1: Details --}}
    <div class="em-wizard__panel is-active" data-wizard-panel="1">
        <div class="em-form-block">
            <h3 class="em-form-block__title">Campaign details</h3>
            <p class="em-form-block__desc">Internal name and what recipients see in their inbox.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-sm" for="name">Campaign name</label>
                    <input id="name" type="text" name="name" class="form-control radius-8" value="{{ old('name', $c?->name ?? '') }}" required placeholder="March open day invite" data-wizard-required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-sm" for="subject">Email subject</label>
                    <input id="subject" type="text" name="subject" class="form-control radius-8" value="{{ old('subject', $c?->subject ?? '') }}" required placeholder="Join us for our open day" data-wizard-required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-sm" for="from_name">From name</label>
                    <input id="from_name" type="text" name="from_name" class="form-control radius-8" value="{{ old('from_name', $c?->from_name ?? '') }}" placeholder="Admissions Team">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-sm" for="from_email">From email</label>
                    <input id="from_email" type="email" name="from_email" class="form-control radius-8" value="{{ old('from_email', $c?->from_email ?? '') }}" placeholder="admissions@school.com">
                </div>
            </div>
        </div>
    </div>

    {{-- Step 2: Audience --}}
    <div class="em-wizard__panel" data-wizard-panel="2" hidden>
        @include('admin.email-marketing.partials.audience-picker', [
            'filters' => $filters,
            'currentSource' => $currentSource,
            'leadStatuses' => $leadStatuses,
            'leadPriorities' => $leadPriorities,
        ])
    </div>

    {{-- Step 3: Design --}}
    <div class="em-wizard__panel" data-wizard-panel="3" hidden>
        <div class="em-form-block mb-16">
            <h3 class="em-form-block__title">Design your email</h3>
            <p class="em-form-block__desc">Use the editor and live preview. Insert merge tags and content blocks with one click.</p>
        </div>
        @include('admin.email-marketing.partials.template-studio', [
            'showGallery' => true,
            'templates' => $templates,
            'bodyHtml' => old('body_html', $c?->body_html ?? ''),
            'selectedTemplateId' => old('template_id', $c?->template_id ?? ''),
        ])
        <div class="form-check mt-16">
            <input class="form-check-input" type="checkbox" name="tracking_enabled" value="1" id="tracking_enabled" @checked(old('tracking_enabled', $c?->tracking_enabled ?? true))>
            <label class="form-check-label" for="tracking_enabled">Track opens and link clicks (SendGrid)</label>
        </div>
    </div>

    {{-- Step 4: Review --}}
    <div class="em-wizard__panel" data-wizard-panel="4" hidden>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="em-review-card">
                    <h4>Campaign summary</h4>
                    <dl class="em-review-list">
                        <div><dt>Name</dt><dd data-review-name>—</dd></div>
                        <div><dt>Subject</dt><dd data-review-subject>—</dd></div>
                        <div><dt>From</dt><dd data-review-from>—</dd></div>
                        <div><dt>Audience</dt><dd data-review-audience>—</dd></div>
                        <div><dt>Tracking</dt><dd data-review-tracking>—</dd></div>
                    </dl>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="em-review-card">
                    <h4>Delivery estimate</h4>
                    <div class="em-review-stats">
                        <div><strong data-review-eligible>—</strong><span>Will receive</span></div>
                        <div><strong data-review-selected>—</strong><span>Selected</span></div>
                        <div><strong data-review-excluded>—</strong><span>Excluded</span></div>
                    </div>
                    <p class="text-secondary-light text-sm mb-0 mt-12" data-review-preflight-msg>Checking audience…</p>
                </div>
                <div class="em-review-card mt-16">
                    <h4>Email preview</h4>
                    <div class="em-review-preview" data-review-preview></div>
                </div>
            </div>
        </div>
    </div>
</div>
