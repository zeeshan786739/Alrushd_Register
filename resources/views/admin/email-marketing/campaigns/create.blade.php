@extends('admin.layouts.app')
@section('title', 'Create Campaign')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'campaigns',
    'shellTitle' => 'Create campaign',
    'shellSubtitle' => 'A guided 4-step flow — details, audience, design, and review.',
    'shellActions' => [[
        'label' => 'Back to campaigns',
        'url' => route('admin.email.campaigns.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form method="POST" action="{{ route('admin.email.campaigns.store') }}" class="em-campaign-form" data-campaign-form>
    @csrf
    <div class="em-panel p-24">
        @include('admin.email-marketing.campaigns._form', [
            'campaign' => null,
            'templates' => $templates,
            'leadStatuses' => $leadStatuses,
            'leadPriorities' => $leadPriorities,
        ])
    </div>

    <div class="em-wizard-alert" data-wizard-error hidden role="alert"></div>

    <div class="em-wizard-actions" data-wizard-actions>
        <button type="button" class="btn btn-outline-neutral-500 radius-8 fc-btn" data-wizard-back hidden>
            <iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon> Back
        </button>
        <div class="em-wizard-actions__spacer"></div>
        <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-outline-neutral-500 radius-8 fc-btn">Cancel</a>
        <button type="button" class="btn btn-primary-600 radius-8 fc-btn" data-wizard-next>
            Continue <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </button>
        <button type="submit" class="btn btn-primary-600 radius-8 fc-btn" name="send_now" value="0" data-wizard-submit hidden style="display:none">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon> Save draft
        </button>
        <button type="submit" class="btn btn-outline-primary-600 radius-8 fc-btn" name="send_now" value="1" data-wizard-send hidden style="display:none">
            <iconify-icon icon="solar:plain-linear"></iconify-icon> Save &amp; send
        </button>
    </div>
</form>
@endsection

@section('script')
@include('admin.email-marketing.partials.audience-picker-script')
@include('admin.email-marketing.partials.template-studio-script')
@include('admin.email-marketing.partials.campaign-wizard-script')
@endsection
