@php
    $folder = $folder ?? 'inbox';
    $counts = $counts ?? ['inbox'=>0,'inbox_unread'=>0,'sent'=>0,'draft'=>0,'starred'=>0];
@endphp
<style>
.em-shell{display:grid;grid-template-columns:220px 1fr;gap:16px}
.em-nav a{display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:8px;color:inherit;text-decoration:none}
.em-nav a.is-active,.em-nav a:hover{background:rgba(15,39,74,.06)}
.em-nav .em-badge{margin-left:auto;font-size:12px;opacity:.7}
.em-list-item{display:block;padding:14px 16px;border-bottom:1px solid rgba(0,0,0,.06);text-decoration:none;color:inherit}
.em-list-item:hover{background:rgba(15,39,74,.03)}
.em-list-item.unread{font-weight:600}
.em-meta{font-size:12px;opacity:.65}
@media (max-width:991px){.em-shell{grid-template-columns:1fr}.em-nav{display:flex;flex-wrap:wrap;gap:4px}.em-nav a{padding:8px 10px}}
</style>
<div class="em-shell mb-24">
    <aside class="card radius-12 shadow-2 border-0 em-nav p-12">
        @can('compose emails')
        <a href="{{ route('admin.email.compose') }}" class="btn btn-primary-600 radius-8 w-100 mb-12 justify-content-center">Compose</a>
        @endcan
        @can('view inbox')
        <a href="{{ route('admin.email.inbox') }}" class="{{ $folder==='inbox'?'is-active':'' }}">
            <iconify-icon icon="solar:inbox-linear"></iconify-icon> Inbox
            <span class="em-badge">{{ $counts['inbox_unread'] ?? 0 }}</span>
        </a>
        @endcan
        @can('star emails')
        <a href="{{ route('admin.email.starred') }}" class="{{ $folder==='starred'?'is-active':'' }}">
            <iconify-icon icon="solar:star-linear"></iconify-icon> Starred
            <span class="em-badge">{{ $counts['starred'] ?? 0 }}</span>
        </a>
        @endcan
        @can('view sent emails')
        <a href="{{ route('admin.email.sent') }}" class="{{ $folder==='sent'?'is-active':'' }}">
            <iconify-icon icon="solar:plain-linear"></iconify-icon> Sent
            <span class="em-badge">{{ $counts['sent'] ?? 0 }}</span>
        </a>
        @endcan
        @can('manage drafts')
        <a href="{{ route('admin.email.drafts') }}" class="{{ $folder==='draft'?'is-active':'' }}">
            <iconify-icon icon="solar:document-linear"></iconify-icon> Drafts
            <span class="em-badge">{{ $counts['draft'] ?? 0 }}</span>
        </a>
        @endcan
        @can('view campaigns')
        <a href="{{ route('admin.email.campaigns.index') }}">
            <iconify-icon icon="solar:megaphone-linear"></iconify-icon> Campaigns
        </a>
        @endcan
        @can('view templates')
        <a href="{{ route('admin.email.templates.index') }}">
            <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon> Templates
        </a>
        @endcan
        @can('manage mailbox settings')
        <a href="{{ route('admin.email.mailbox.settings') }}">
            <iconify-icon icon="solar:settings-linear"></iconify-icon> Settings
        </a>
        @endcan
    </aside>
    <div>
