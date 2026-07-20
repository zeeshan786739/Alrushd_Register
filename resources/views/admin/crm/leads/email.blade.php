@extends('admin.layouts.app')
@section('title', 'Email Lead')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Email Lead',
        'subtitle' => $lead->full_name,
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => $lead->full_name, 'url' => route('admin.crm.leads.show', $lead)],
            ['label' => 'Email'],
        ],
    ])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <div class="mb-20">
                <strong>To:</strong> {{ $lead->email }}
            </div>
            <form method="POST" action="{{ route('admin.crm.leads.email.send', $lead) }}">
                @csrf
                <div class="mb-16">
                    <label class="form-label" for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control radius-8 @error('subject') is-invalid @enderror" value="{{ old('subject', 'Follow up from '.config('app.name')) }}" required>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-24">
                    <label class="form-label" for="message">Message</label>
                    <textarea id="message" name="message" class="form-control radius-8 @error('message') is-invalid @enderror" rows="8" required>{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex gap-12">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11">Send Email</button>
                    <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
