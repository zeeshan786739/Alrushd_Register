@extends('admin.layouts.app')
@section('title', $message->subject ?: 'Message')
@section('content')
@php
    $replySubject = $replySubject ?? (($message->subject && !str_starts_with(strtolower($message->subject), 're:')) ? 'Re: '.$message->subject : ($message->subject ?: ''));
    $replyTo = $replyTo ?? ($message->direction === 'inbound' ? ($message->from_email ?: $message->to) : $message->to);
@endphp
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => $message->subject ?: '(no subject)',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label'=>'Email Marketing'],
            ['label'=>ucfirst($folder === 'draft' ? 'Drafts' : $folder), 'url'=>route('admin.email.'.($folder === 'draft' ? 'drafts' : ($folder === 'sent' ? 'sent' : ($folder === 'starred' ? 'starred' : 'inbox'))))],
            ['label'=>'Message'],
        ],
    ])

    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger radius-8">{{ $errors->first() }}</div>@endif

    @include('admin.email-marketing.partials.nav')

        <div class="card radius-12 shadow-2 border-0">
            <div class="card-body p-24">
                <div class="d-flex flex-wrap gap-8 mb-16">
                    @can('star emails')
                    <form method="POST" action="{{ route('admin.email.star', $message) }}">@csrf
                        <button class="btn btn-sm btn-outline-warning radius-8" aria-label="{{ $message->is_starred ? 'Unstar' : 'Star' }}">
                            <iconify-icon icon="{{ $message->is_starred ? 'solar:star-bold' : 'solar:star-linear' }}"></iconify-icon>
                            {{ $message->is_starred ? 'Starred' : 'Star' }}
                        </button>
                    </form>
                    @endcan
                    @if($message->folder === 'inbox')
                    <form method="POST" action="{{ route('admin.email.unread', $message) }}">@csrf
                        <button class="btn btn-sm btn-outline-secondary radius-8">Mark unread</button>
                    </form>
                    @endif
                    @can('send emails')
                    <button type="button" class="btn btn-sm btn-outline-primary-600 radius-8" onclick="document.getElementById('em-reply').classList.toggle('d-none')">Reply</button>
                    <button type="button" class="btn btn-sm btn-outline-primary-600 radius-8" onclick="document.getElementById('em-forward').classList.toggle('d-none')">Forward</button>
                    @endcan
                    @if($message->folder==='draft')
                    <a href="{{ route('admin.email.compose', ['draft_id'=>$message->id]) }}" class="btn btn-sm btn-primary-600 radius-8">Continue editing</a>
                    @endif
                    <form method="POST" action="{{ route('admin.email.destroy', $message) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger-600 radius-8">Delete</button>
                    </form>
                </div>

                <div class="mb-16">
                    <div><strong>From:</strong> {{ $message->from_name }} &lt;{{ $message->from_email }}&gt;</div>
                    <div><strong>To:</strong> {{ $message->to }}</div>
                    @if($message->cc)<div><strong>Cc:</strong> {{ $message->cc }}</div>@endif
                    <div class="em-meta">{{ optional($message->sent_at ?: $message->received_at ?: $message->created_at)->format('M j, Y g:i A') }}</div>
                    @if($message->folder === 'sent' || $message->provider_status || $message->delivery_status)
                        <div class="mt-8 d-flex flex-wrap gap-8 align-items-center">
                            @include('admin.email-marketing.partials.provider-status-pill', [
                                'status' => $message->provider_status ?: $message->delivery_status ?: 'pending',
                            ])
                            @if($message->delivered_at)<span class="em-meta">Delivered {{ $message->delivered_at->format('M j, Y g:i A') }}</span>@endif
                            @if($message->opened_at)<span class="em-meta">Opened {{ $message->opened_at->format('M j, Y g:i A') }}</span>@endif
                            @if($message->clicked_at)<span class="em-meta">Clicked {{ $message->clicked_at->format('M j, Y g:i A') }}</span>@endif
                            @if($message->bounced_at)<span class="em-meta text-danger">Bounced {{ $message->bounced_at->format('M j, Y g:i A') }}</span>@endif
                        </div>
                    @endif
                </div>

                @include('admin.email-marketing.partials.provider-timeline', ['events' => $providerEvents ?? []])

                @if($message->lead || $message->customer || $message->quotation || $message->invoice)
                <div class="border rounded-8 p-12 mb-16" style="background:rgba(15,39,74,.03)">
                    <div class="em-meta mb-8">Related CRM</div>
                    <div class="d-flex flex-wrap gap-12">
                        @if($message->lead)
                            <a href="{{ route('admin.crm.leads.show', $message->lead) }}">Lead: {{ $message->lead->full_name ?? trim(($message->lead->first_name ?? '').' '.($message->lead->last_name ?? '')) }}</a>
                        @endif
                        @if($message->customer)
                            <a href="{{ route('admin.crm.customers.show', $message->customer) }}">Customer: {{ $message->customer->name }}</a>
                        @endif
                        @if($message->quotation)
                            <a href="{{ route('admin.crm.quotations.show', $message->quotation) }}">Quotation: {{ $message->quotation->quotation_number }}</a>
                        @endif
                        @if($message->invoice)
                            <a href="{{ route('admin.crm.invoices.show', $message->invoice) }}">Invoice: {{ $message->invoice->invoice_number }}</a>
                        @endif
                    </div>
                </div>
                @endif

                <div class="border-top pt-16 mb-24 em-reader-body" style="overflow-wrap:anywhere">
                    @if($sanitizedBody)
                        {!! $sanitizedBody !!}
                    @elseif($message->body_text)
                        {!! nl2br(e($message->body_text)) !!}
                    @else
                        <span class="em-meta">No message body.</span>
                    @endif
                </div>

                @if($message->attachments->isNotEmpty())
                <div class="mb-24">
                    <h6 class="fw-semibold">Attachments</h6>
                    <ul class="list-unstyled mb-0">
                        @foreach($message->attachments as $attachment)
                            <li class="mb-8">
                                <a href="{{ route('admin.email.attachments.download', [$message, $attachment]) }}">
                                    <iconify-icon icon="solar:download-linear"></iconify-icon>
                                    {{ $attachment->original_name }}
                                </a>
                                <span class="em-meta">({{ number_format($attachment->size/1024,1) }} KB)</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @can('send emails')
                <div id="em-reply" class="d-none border-top pt-16 mb-16">
                    <h6>Reply</h6>
                    <form method="POST" action="{{ route('admin.email.reply', $message) }}">@csrf
                        <div class="em-meta mb-8">To: {{ $replyTo }}</div>
                        <label class="form-label" for="reply_subject">Subject</label>
                        <input id="reply_subject" type="text" name="subject" value="{{ $replySubject }}" class="form-control radius-8 mb-12">
                        <label class="form-label" for="reply_body">Message</label>
                        <textarea id="reply_body" name="body_html" class="form-control radius-8 mb-12" rows="5" required></textarea>
                        <button class="btn btn-primary-600 radius-8">Send Reply</button>
                    </form>
                </div>
                <div id="em-forward" class="d-none border-top pt-16">
                    <h6>Forward</h6>
                    <form method="POST" action="{{ route('admin.email.forward', $message) }}">@csrf
                        <label class="form-label" for="fwd_to">To</label>
                        <input id="fwd_to" type="text" name="to" class="form-control radius-8 mb-12" required>
                        <label class="form-label" for="fwd_body">Note</label>
                        <textarea id="fwd_body" name="body_html" class="form-control radius-8 mb-12" rows="3"></textarea>
                        <button class="btn btn-primary-600 radius-8">Forward</button>
                    </form>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
</div>
@endsection
