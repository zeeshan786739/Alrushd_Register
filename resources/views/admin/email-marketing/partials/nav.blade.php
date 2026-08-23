@php
    use App\Models\EmailMarketing\Message;
    $folder = $folder ?? 'inbox';
    $showInboxFolders = $showInboxFolders ?? in_array($folder, ['inbox', 'sent', 'draft', 'starred'], true);
    $skipModuleNav = $skipModuleNav ?? false;

    if (! isset($counts) || ! is_array($counts)) {
        try {
            $base = Message::forCurrentOrganization();
            $counts = [
                'inbox' => (clone $base)->inbox()->count(),
                'inbox_unread' => (clone $base)->inbox()->unread()->count(),
                'sent' => (clone $base)->sent()->count(),
                'draft' => (clone $base)->draft()->count(),
                'starred' => (clone $base)->starred()->count(),
            ];
        } catch (\Throwable $e) {
            $counts = ['inbox'=>0,'inbox_unread'=>0,'sent'=>0,'draft'=>0,'starred'=>0];
        }
    }

    $emActiveTab = $emActiveTab ?? (match ($folder) {
        'campaigns' => 'campaigns',
        'templates' => 'templates',
        'settings' => 'settings',
        default => 'inbox',
    });
@endphp

@if(! $skipModuleNav)
    @include('admin.email-marketing.partials.module-nav', ['activeTab' => $emActiveTab])
@endif

@if($showInboxFolders)
<div class="em-shell mb-24">
    <aside class="em-folder-nav">
        @can('compose emails')
        <a href="{{ route('admin.email.compose') }}" class="btn btn-primary-600 radius-8 w-100 mb-12 justify-content-center fc-btn">
            <iconify-icon icon="solar:pen-new-square-linear"></iconify-icon> Compose
        </a>
        @endcan
        @can('view inbox')
        <a href="{{ route('admin.email.inbox') }}" class="em-folder-nav__link {{ $folder==='inbox'?'is-active':'' }}">
            <iconify-icon icon="solar:inbox-linear"></iconify-icon> Inbox
            <span class="em-folder-nav__badge">{{ $counts['inbox_unread'] ?? 0 }}</span>
        </a>
        @endcan
        @can('star emails')
        <a href="{{ route('admin.email.starred') }}" class="em-folder-nav__link {{ $folder==='starred'?'is-active':'' }}">
            <iconify-icon icon="solar:star-linear"></iconify-icon> Starred
            <span class="em-folder-nav__badge">{{ $counts['starred'] ?? 0 }}</span>
        </a>
        @endcan
        @can('view sent emails')
        <a href="{{ route('admin.email.sent') }}" class="em-folder-nav__link {{ $folder==='sent'?'is-active':'' }}">
            <iconify-icon icon="solar:plain-linear"></iconify-icon> Sent
            <span class="em-folder-nav__badge">{{ $counts['sent'] ?? 0 }}</span>
        </a>
        @endcan
        @can('manage drafts')
        <a href="{{ route('admin.email.drafts') }}" class="em-folder-nav__link {{ $folder==='draft'?'is-active':'' }}">
            <iconify-icon icon="solar:document-linear"></iconify-icon> Drafts
            <span class="em-folder-nav__badge">{{ $counts['draft'] ?? 0 }}</span>
        </a>
        @endcan
    </aside>
    <div class="em-shell__content">
@endif
