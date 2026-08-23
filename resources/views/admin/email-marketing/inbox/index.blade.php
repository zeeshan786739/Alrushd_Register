@extends('admin.layouts.app')
@section('title', $title)
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => $title,
        'subtitle' => 'Email Marketing',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>$title]],
        'actions' => array_filter([
            $folder==='inbox' && auth('admin')->user()?->can('sync inbox')
                ? ['label'=>'Refresh','url'=>'#','icon'=>'solar:refresh-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11','attrs'=>'onclick="document.getElementById(\'em-sync-form\').submit();return false;"']
                : null,
        ]),
    ])

    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger radius-8">{{ session('error') }}</div>@endif

    @can('sync inbox')
    <form id="em-sync-form" method="POST" action="{{ route('admin.email.inbox.sync') }}" class="d-none">@csrf</form>
    @endcan

    @include('admin.email-marketing.partials.nav')

        <div class="card radius-12 shadow-2 border-0">
            <div class="card-body p-16">
                <form method="GET" class="mb-16">
                    <div class="input-group">
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control radius-8" placeholder="Search subject, sender, recipient" aria-label="Search emails">
                        <button class="btn btn-outline-primary-600 radius-8">Search</button>
                    </div>
                </form>

                @forelse($messages as $msg)
                    <a href="{{ route('admin.email.show', $msg) }}" class="em-list-item {{ !$msg->is_read && $msg->folder==='inbox' ? 'unread' : '' }}">
                        <div class="d-flex justify-content-between gap-12">
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex align-items-center gap-8 flex-wrap">
                                    <span>{{ $msg->folder==='sent' || $msg->folder==='draft' ? ($msg->to ?: 'No recipients') : ($msg->from_name ?: $msg->from_email ?: 'Unknown') }}</span>
                                    @if(($msg->attachments_count ?? 0) > 0)
                                        <iconify-icon icon="solar:paperclip-linear" class="em-meta" title="Has attachments"></iconify-icon>
                                    @endif
                                    @if($msg->is_starred)
                                        <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>
                                    @endif
                                </div>
                                <div>{{ $msg->subject ?: '(no subject)' }}</div>
                                <div class="em-meta text-truncate" style="max-width:520px">{{ \Illuminate\Support\Str::limit($msg->body_text ?: strip_tags((string)$msg->body_html), 100) }}</div>
                                @if($msg->folder==='sent')
                                    <div class="mt-4">
                                        @if($msg->lead_id)<span class="em-crm-chip">Lead</span>@endif
                                        @if($msg->customer_id)<span class="em-crm-chip">Customer</span>@endif
                                        @if($msg->quotation_id)<span class="em-crm-chip">Quotation</span>@endif
                                        @if($msg->invoice_id)<span class="em-crm-chip">Invoice</span>@endif
                                    </div>
                                @endif
                            </div>
                            <div class="em-meta text-nowrap text-end">
                                {{ optional($msg->sent_at ?: $msg->received_at ?: $msg->created_at)->diffForHumans() }}
                                @if($msg->folder==='sent')
                                    <div class="mt-4">
                                        @include('admin.email-marketing.partials.provider-status-pill', [
                                            'status' => $msg->provider_status ?: $msg->delivery_status ?: 'pending',
                                        ])
                                    </div>
                                    @if($msg->delivered_at)<div class="mt-4">Delivered {{ $msg->delivered_at->diffForHumans() }}</div>@endif
                                    @if($msg->opened_at)<div>Opened {{ $msg->opened_at->diffForHumans() }}</div>@endif
                                    @if($msg->clicked_at)<div>Clicked {{ $msg->clicked_at->diffForHumans() }}</div>@endif
                                    @if($msg->bounced_at)<div class="text-danger">Bounced {{ $msg->bounced_at->diffForHumans() }}</div>@endif
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-40 text-secondary-light">No messages in {{ strtolower($title) }}.</div>
                @endforelse

                <div class="mt-16">{{ $messages->links() }}</div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
