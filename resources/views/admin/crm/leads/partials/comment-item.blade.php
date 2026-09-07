<article class="crm-ticket-comment" data-crm-comment-id="{{ $note->id }}">
    <span class="crm-ticket-comment__avatar" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($note->admin?->name ?? 'Team') }}</span>
    <div class="crm-ticket-comment__body">
        <header class="crm-ticket-comment__head">
            <strong>{{ $note->admin?->name ?? 'Team member' }}</strong>
            <time datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->diffForHumans() }}</time>
        </header>
        <div class="crm-ticket-comment__content">{!! \App\Support\LeadCommentFormatter::body($note->note, $admins) !!}</div>
    </div>
</article>
