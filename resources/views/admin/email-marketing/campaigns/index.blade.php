@extends('admin.layouts.app')
@section('title', 'Campaigns')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Campaigns',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Campaigns']],
        'actions' => array_filter([
            auth('admin')->user()?->can('create campaigns')
                ? ['label'=>'New Campaign','url'=>route('admin.email.campaigns.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']
                : null,
        ]),
    ])
    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th>Recipients</th><th>Sent</th><th>Opens</th><th>Clicks</th><th></th></tr></thead>
                    <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td><a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="fw-semibold">{{ $campaign->name }}</a></td>
                            <td>{{ $campaign->subject }}</td>
                            <td><span class="badge bg-neutral-200">{{ $campaign->status }}</span></td>
                            <td>{{ $campaign->recipient_count }}</td>
                            <td>{{ $campaign->sent_count }}</td>
                            <td>{{ $campaign->opened_count }}</td>
                            <td>{{ $campaign->clicked_count }}</td>
                            <td><a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-40 text-secondary-light">No campaigns yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-16">{{ $campaigns->links() }}</div>
</div>
@endsection
