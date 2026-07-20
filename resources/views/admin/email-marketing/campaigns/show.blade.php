@extends('admin.layouts.app')
@section('title', $campaign->name)
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => $campaign->name,
        'subtitle' => $campaign->subject,
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Campaigns','url'=>route('admin.email.campaigns.index')],['label'=>$campaign->name]],
    ])
    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger radius-8">{{ $errors->first() }}</div>@endif

    <div class="row g-3 mb-24">
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Status</div><strong>{{ $campaign->status }}</strong></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Recipients</div><strong>{{ $campaign->recipient_count }}</strong></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Sent / Failed</div><strong>{{ $campaign->sent_count }} / {{ $campaign->failed_count }}</strong></div></div></div>
        <div class="col-md-3"><div class="card radius-12 border-0 shadow-2"><div class="card-body"><div class="text-sm">Opens / Clicks</div><strong>{{ $campaign->opened_count }} / {{ $campaign->clicked_count }}</strong></div></div></div>
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Email</th><th>Name</th><th>Status</th><th>Opened</th><th>Clicked</th><th>Sent at</th></tr></thead>
                    <tbody>
                    @forelse($campaign->recipients as $recipient)
                        <tr>
                            <td>{{ $recipient->email }}</td>
                            <td>{{ $recipient->name ?? '—' }}</td>
                            <td>{{ $recipient->status }}</td>
                            <td>{{ $recipient->is_opened ? 'Yes' : 'No' }}</td>
                            <td>{{ $recipient->is_clicked ? 'Yes' : 'No' }}</td>
                            <td>{{ optional($recipient->sent_at)->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-24 text-secondary-light">No recipients snapshotted.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
