@extends('admin.layouts.app')
@section('title', 'Compose')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Compose',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Compose']],
    ])

    @if($errors->any())
        <div class="alert alert-danger radius-8">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif

    @include('admin.email-marketing.partials.nav')

        <div class="card radius-12 shadow-2 border-0">
            <div class="card-body p-24">
                <form method="POST" action="{{ route('admin.email.send') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="draft_id" value="{{ $prefill['draft_id'] ?? '' }}">
                    <input type="hidden" name="lead_id" value="{{ $prefill['lead_id'] ?? '' }}">
                    <input type="hidden" name="customer_id" value="{{ $prefill['customer_id'] ?? '' }}">

                    <div class="mb-16">
                        <label class="form-label" for="to">To</label>
                        <input id="to" type="text" name="to" class="form-control radius-8" value="{{ old('to', $prefill['to'] ?? '') }}" required placeholder="email@example.com">
                    </div>
                    <div class="row g-3 mb-16">
                        <div class="col-md-6">
                            <label class="form-label" for="cc">Cc</label>
                            <input id="cc" type="text" name="cc" class="form-control radius-8" value="{{ old('cc', $prefill['cc'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bcc">Bcc</label>
                            <input id="bcc" type="text" name="bcc" class="form-control radius-8" value="{{ old('bcc', $prefill['bcc'] ?? '') }}">
                        </div>
                    </div>
                    <div class="mb-16">
                        <label class="form-label" for="subject">Subject</label>
                        <input id="subject" type="text" name="subject" class="form-control radius-8" value="{{ old('subject', $prefill['subject'] ?? '') }}" required>
                    </div>
                    <div class="mb-16">
                        <label class="form-label" for="body_html">Message</label>
                        <textarea id="body_html" name="body_html" class="form-control radius-8" rows="12">{{ old('body_html', $prefill['body_html'] ?? '') }}</textarea>
                    </div>
                    <div class="mb-24">
                        <label class="form-label" for="attachments">Attachments</label>
                        <input id="attachments" type="file" name="attachments[]" class="form-control radius-8" multiple>
                        <div class="form-text">PDF, images, Word/Excel up to 10MB each.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-12">
                        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11">Send</button>
                        <button type="submit" formaction="{{ route('admin.email.draft.save') }}" class="btn btn-outline-primary-600 radius-8 px-24 py-11">Save Draft</button>
                        <a href="{{ route('admin.email.inbox') }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
