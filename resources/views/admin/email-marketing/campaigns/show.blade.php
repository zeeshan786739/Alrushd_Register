@extends('admin.layouts.app')
@section('title', $campaign->name)
@section('content')
@php
    /** @var \App\Support\CampaignAnalyticsSummary $analytics */
    $fmtRate = fn (?float $r) => $r === null ? '—' : $r.'%';
@endphp
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => $campaign->name,
        'subtitle' => $campaign->subject,
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Campaigns','url'=>route('admin.email.campaigns.index')],['label'=>$campaign->name]],
    ])
    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger radius-8">{{ $errors->first() }}</div>@endif

    @php $folder = 'campaigns'; @endphp
    @include('admin.email-marketing.partials.nav')

    @if(empty($asmConfigured) && in_array($campaign->status, ['draft','scheduled']))
        <div class="alert alert-warning radius-8">SendGrid ASM unsubscribe group is not configured for this organization. Marketing sends will still queue, but configure the group ID under Mailbox Settings for provider-side unsubscribe management.</div>
    @endif

    <div class="row g-3 mb-16">
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Status</div><strong class="text-capitalize">{{ $campaign->status }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Selected</div><strong>{{ $analytics->selected }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Processed</div><strong>{{ $analytics->processed }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Delivered</div><strong>{{ $analytics->delivered }}</strong><div class="text-sm opacity-75">{{ $fmtRate($analytics->deliveryRate()) }}</div></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Opened</div><strong>{{ $analytics->opened }}</strong><div class="text-sm opacity-75">{{ $fmtRate($analytics->openRate()) }}</div></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Clicked</div><strong>{{ $analytics->clicked }}</strong><div class="text-sm opacity-75">{{ $fmtRate($analytics->clickRate()) }}</div></div></div></div>
    </div>
    <div class="row g-3 mb-24">
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Deferred</div><strong>{{ $analytics->deferred }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Bounced</div><strong>{{ $analytics->bounced }}</strong><div class="text-sm opacity-75">{{ $fmtRate($analytics->bounceRate()) }}</div></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Dropped</div><strong>{{ $analytics->dropped }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Unsubscribed</div><strong>{{ $analytics->unsubscribed }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Failed</div><strong>{{ $analytics->failed }}</strong></div></div></div>
        <div class="col-6 col-md-3 col-xl-2"><div class="card radius-12 border-0 shadow-2"><div class="card-body p-16"><div class="text-sm">Skipped</div><strong>{{ $analytics->skipped }}</strong></div></div></div>
    </div>

    <div class="d-flex flex-wrap gap-8 mb-24">
        @if(in_array($campaign->status, ['draft','scheduled']))
            @can('send campaigns')
            <form method="POST" action="{{ route('admin.email.campaigns.send', $campaign) }}" onsubmit="return confirm('Queue this campaign for delivery?')">@csrf
                <button class="btn btn-primary-600 radius-8">Send now</button>
            </form>
            <form method="POST" action="{{ route('admin.email.campaigns.schedule', $campaign) }}" class="d-flex gap-8">@csrf
                <label class="visually-hidden" for="scheduled_at">Schedule</label>
                <input id="scheduled_at" type="datetime-local" name="scheduled_at" class="form-control radius-8" required>
                <button class="btn btn-outline-primary-600 radius-8">Schedule</button>
            </form>
            @endcan
            @can('update campaigns')
            <a href="{{ route('admin.email.campaigns.edit', $campaign) }}" class="btn btn-outline-primary-600 radius-8">Edit</a>
            @endcan
        @endif
        @can('create campaigns')
        <form method="POST" action="{{ route('admin.email.campaigns.duplicate', $campaign) }}">@csrf
            <button class="btn btn-outline-neutral-500 radius-8">Duplicate</button>
        </form>
        @endcan
        @can('delete campaigns')
        <form method="POST" action="{{ route('admin.email.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete campaign?')">@csrf @method('DELETE')
            <button class="btn btn-outline-danger-600 radius-8">Delete</button>
        </form>
        @endcan
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-12">Content preview</h6>
            <div style="overflow-wrap:anywhere">{!! app(\App\Services\EmailMarketing\HtmlSanitizer::class)->sanitize($campaign->body_html) !!}</div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-16 border-bottom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" for="recipient_search">Search</label>
                    <input id="recipient_search" type="search" name="search" value="{{ request('search') }}" class="form-control radius-8" placeholder="Email or name">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="recipient_status">Status</label>
                    <select id="recipient_status" name="status" class="form-select radius-8">
                        <option value="">All</option>
                        @foreach(['pending','queued','sent','failed','skipped','delivered','bounce','dropped','open','click','unsubscribe'] as $st)
                            <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><button class="btn btn-outline-primary-600 radius-8 w-100">Filter</button></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Status</th>
                        <th>Delivered</th>
                        <th>Opened</th>
                        <th>Clicked</th>
                        <th>Last event</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recipients as $recipient)
                    @php
                        $pill = $recipient->provider_status ?: $recipient->status;
                        $tone = match ($pill) {
                            'delivered', 'open', 'click', 'sent' => 'success',
                            'queued', 'pending', 'processed', 'deferred' => 'warning',
                            'bounce', 'dropped', 'failed', 'spamreport' => 'danger',
                            'skipped', 'unsubscribe', 'group_unsubscribe' => 'neutral',
                            default => 'neutral',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div>{{ $recipient->name ?: '—' }}</div>
                            <div class="text-sm opacity-75">{{ $recipient->email }}</div>
                        </td>
                        <td><span class="badge text-bg-{{ $tone === 'neutral' ? 'secondary' : ($tone === 'success' ? 'success' : ($tone === 'warning' ? 'warning' : 'danger')) }} text-capitalize">{{ str_replace('_',' ', $pill) }}</span></td>
                        <td>{{ optional($recipient->delivered_at)->format('M j, g:i A') ?? '—' }}</td>
                        <td>{{ optional($recipient->opened_at)->format('M j, g:i A') ?? ($recipient->is_opened ? 'Yes' : '—') }}</td>
                        <td>{{ optional($recipient->clicked_at)->format('M j, g:i A') ?? ($recipient->is_clicked ? 'Yes' : '—') }}</td>
                        <td>
                            <div>{{ optional($recipient->sent_at ?: $recipient->delivered_at ?: $recipient->opened_at ?: $recipient->clicked_at ?: $recipient->bounced_at)->format('M j, Y g:i A') ?? '—' }}</div>
                            @if($recipient->error_message)
                                <div class="text-sm text-danger">{{ \Illuminate\Support\Str::limit($recipient->error_message, 80) }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-24 text-secondary-light">No recipients snapshotted.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($recipients, 'links'))
            <div class="card-body">{{ $recipients->links() }}</div>
        @endif
    </div>
    </div>
</div>
</div>
@endsection
