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
                            <div>
                                <div>{{ $msg->folder==='sent' || $msg->folder==='draft' ? ($msg->to ?: 'No recipients') : ($msg->from_name ?: $msg->from_email ?: 'Unknown') }}</div>
                                <div>{{ $msg->subject ?: '(no subject)' }}</div>
                                <div class="em-meta text-truncate" style="max-width:520px">{{ \Illuminate\Support\Str::limit($msg->body_text ?: strip_tags((string)$msg->body_html), 100) }}</div>
                            </div>
                            <div class="em-meta text-nowrap">
                                @if($msg->is_starred)<iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>@endif
                                {{ optional($msg->sent_at ?: $msg->received_at ?: $msg->created_at)->diffForHumans() }}
                                @if($msg->folder==='sent' && $msg->delivery_status)
                                    <div><span class="badge bg-neutral-200">{{ $msg->delivery_status }}</span></div>
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
