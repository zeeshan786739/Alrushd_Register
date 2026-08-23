@extends('admin.layouts.app')
@section('title', 'Campaigns')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'campaigns',
    'shellTitle' => 'Campaigns',
    'shellSubtitle' => 'Create broadcasts, track opens and clicks, and reach your CRM audience.',
    'shellActions' => array_values(array_filter([
        auth('admin')->user()?->can('create campaigns') ? [
            'label' => 'New campaign',
            'url' => route('admin.email.campaigns.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ] : null,
    ])),
])

<div class="em-panel">
    <div class="em-panel__head">
        <div>
            <h2 class="em-panel__title">All campaigns</h2>
            <p class="em-panel__desc">Draft, scheduled, and sent broadcasts for your school.</p>
        </div>
        <form method="GET" class="em-inline-filter">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control radius-8" placeholder="Search campaigns…">
            <select name="status" class="form-select radius-8" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['draft','scheduled','sending','sent','failed','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($campaigns->isEmpty())
        <div class="em-empty-state">
            <iconify-icon icon="solar:megaphone-linear"></iconify-icon>
            <h3>No campaigns yet</h3>
            <p>Send your first email broadcast to leads, customers, or form submissions.</p>
            @can('create campaigns')
            <a href="{{ route('admin.email.campaigns.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn">
                <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Create campaign
            </a>
            @endcan
        </div>
    @else
        <div class="table-responsive">
            <table class="table mb-0 align-middle em-table">
                <thead>
                    <tr>
                        <th class="ps-24">Campaign</th>
                        <th>Status</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Opens</th>
                        <th>Clicks</th>
                        <th class="pe-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                    <tr>
                        <td class="ps-24">
                            <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="fw-semibold text-decoration-none">{{ $campaign->name }}</a>
                            <div class="text-secondary-light text-sm">{{ $campaign->subject }}</div>
                        </td>
                        <td><span class="em-status-pill em-status-pill--{{ $campaign->status }}">{{ ucfirst($campaign->status) }}</span></td>
                        <td>{{ number_format($campaign->recipient_count) }}</td>
                        <td>{{ number_format($campaign->sent_count) }}</td>
                        <td>{{ number_format($campaign->opened_count) }}</td>
                        <td>{{ number_format($campaign->clicked_count) }}</td>
                        <td class="pe-24 text-end">
                            <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Open</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-20">{{ $campaigns->links() }}</div>
    @endif
</div>
@endsection
