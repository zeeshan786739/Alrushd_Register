@php
    use App\Support\UserManagementHelper;
    $canEdit = auth()->user()->can('edit user');
    $showActions = $showActions ?? true;
    $compact = $compact ?? false;
@endphp
<article
    class="um-list-row fc-form-row {{ $compact ? 'um-list-row--overview' : 'um-list-row--team' }}"
    @if($canEdit)
        data-um-team-open
        data-href="{{ route('admin.users.edit', $user->id) }}"
        tabindex="0"
        role="link"
    @endif
    aria-label="Open {{ $user->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="um-user-avatar" style="background: {{ UserManagementHelper::avatarGradient($user->email) }};" aria-hidden="true">
            {{ UserManagementHelper::initials($user->name) }}
        </span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $user->name }}</span>
                @if((int) $user->id === (int) auth('admin')->id())
                    <span class="um-you-badge">You</span>
                @endif
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">
                    <iconify-icon icon="solar:letter-linear" aria-hidden="true"></iconify-icon>
                    {{ $user->email }}
                </span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        @forelse($user->roles->take($compact ? 1 : 3) as $role)
            <span class="um-role-badge">{{ UserManagementHelper::formatRoleName($role->name) }}</span>
        @empty
            <span class="um-muted-pill">No role</span>
        @endforelse
    </div>
    @unless($compact)
        <div class="crm-list-row__field crm-list-row__field--date">
            @if($user->last_login_at)
                <span class="crm-list-row__date" title="{{ $user->last_login_at->format('d M Y, H:i') }}">{{ $user->last_login_at->diffForHumans() }}</span>
                <span class="crm-list-row__date-sub">Last login</span>
            @else
                <span class="crm-list-row__date">Never</span>
                <span class="crm-list-row__date-sub">Last login</span>
            @endif
        </div>
        <div class="crm-list-row__actions">
            <div class="crm-list-row__action-group">
                @if($canEdit)
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="crm-list-action" onclick="event.stopPropagation()"><iconify-icon icon="solar:pen-linear"></iconify-icon></a>
                @endif
                @if($showActions && auth()->user()->can('delete user') && (int) $user->id !== (int) auth('admin')->id())
                    <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')</form>
                    <button type="button" class="crm-list-action is-delete delete-btn" data-id="{{ $user->id }}"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                @endif
            </div>
            @if($canEdit)
                <a href="{{ route('admin.users.edit', $user->id) }}" class="crm-list-row__chevron" onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
                </a>
            @endif
        </div>
    @else
        <div class="crm-list-row__actions">
            @if($canEdit)
                <a href="{{ route('admin.users.edit', $user->id) }}" class="crm-list-row__chevron" onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
                </a>
            @endif
        </div>
    @endunless
</article>
