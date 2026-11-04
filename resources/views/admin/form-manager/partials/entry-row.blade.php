@php
    $summary = \App\Support\FormEntryBrowse::summary($entry, $facets);
    $primary = $summary['facets']['role'] ?? null;
    $secondary = $primary ? ($summary['facets']['subject'] ?? null) : null;
    $primary = $primary ?? ($summary['facets']['subject'] ?? null);
    $yearGroup = $summary['facets']['year_group'] ?? null;
    $displayName = $summary['name'] ?? 'Name not provided';
    $submittedAt = $entry->submitted_at ?? $entry->created_at;
    $entryUrl = route('admin.form-manager.entries.show', [$form, $entry]);
    $status = in_array($entry->status, ['pending', 'approved', 'rejected'], true) ? $entry->status : 'pending';
    $user = auth('admin')->user();
@endphp
<article class="fc-subs-row" data-status="{{ $status }}" data-fc-row-href="{{ $entryUrl }}">
    <div class="fc-subs-applicant">
        <span @class(['fc-subs-avatar', 'is-empty' => ! $summary['name']]) aria-hidden="true">
            @if($summary['name'])
                {{ \App\Support\UserManagementHelper::initials($summary['name']) }}
            @else
                <iconify-icon icon="solar:user-linear"></iconify-icon>
            @endif
        </span>
        <div class="fc-subs-applicant__copy">
            <a href="{{ $entryUrl }}" class="fc-applicant__name" title="{{ $displayName }}">{{ $displayName }}</a>
            @if($summary['email'])
                <span class="fc-applicant__contact" title="{{ $summary['email'] }}">{{ $summary['email'] }}</span>
            @endif
            @if($summary['phone'])
                <span class="fc-applicant__contact">{{ $summary['phone'] }}</span>
            @endif
        </div>
    </div>

    <div class="fc-applied" data-label="{{ $detailsHeading }}">
        @if($primary)
            <span class="fc-applied__role" title="{{ $primary }}">{{ $primary }}</span>
        @endif
        @if($secondary)
            <span class="fc-applied__sub" title="{{ $secondary }}">{{ $secondary }}</span>
        @endif
        @if($yearGroup)
            <span @class(['fc-applied__role' => ! $primary, 'fc-applied__sub' => (bool) $primary])>{{ $yearGroup }}</span>
        @endif
        @if(! $primary && ! $yearGroup)
            <span class="fc-applied__sub" title="{{ $summary['preview'] }}">{{ $summary['preview'] ?? '—' }}</span>
        @endif
    </div>

    <div class="fc-subs-date" title="{{ $submittedAt->format('d M Y, H:i') }}" data-label="Submitted">
        <span class="fc-list-row__date">{{ $submittedAt->format('d M Y') }}</span>
        <span class="fc-list-row__date-sub">{{ $submittedAt->diffForHumans() }}</span>
    </div>

    <div class="fc-subs-status">
        @if($user?->can('update form submissions'))
            <form method="POST" action="{{ route('admin.form-manager.entries.status', [$form, $entry]) }}">
                @csrf @method('PATCH')
                <label class="visually-hidden" for="fc-status-{{ $entry->id }}">Status for {{ $displayName }}</label>
                <select id="fc-status-{{ $entry->id }}" name="status" class="fc-subs-status-select" data-status="{{ $status }}" data-fc-auto-submit>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" data-tone="{{ \App\Support\CrmStatusTone::for($value) }}" data-icon="{{ \App\Support\CrmStatusTone::icon($value) }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        @else
            <span class="fc-subs-status-select is-static" data-status="{{ $status }}">{{ ucfirst($status) }}</span>
        @endif
    </div>

    <div class="fc-subs-actions">
        <a href="{{ $entryUrl }}" class="fc-subs-icon-btn" title="View submission" aria-label="View submission from {{ $displayName }}">
            <iconify-icon icon="solar:eye-linear" aria-hidden="true"></iconify-icon>
        </a>
        @if($user?->can('delete form submissions'))
            <form method="POST"
                  action="{{ route('admin.form-manager.entries.destroy', [$form, $entry]) }}"
                  data-crm-confirm
                  data-confirm-title="Delete this submission?"
                  data-confirm-message="The submission from {{ $displayName }} will be permanently deleted. This cannot be undone."
                  data-confirm-label="Delete"
                  data-confirm-tone="danger"
                  data-confirm-icon="solar:trash-bin-minimalistic-linear">
                @csrf @method('DELETE')
                <button type="submit" class="fc-subs-icon-btn is-danger" title="Delete submission" aria-label="Delete submission from {{ $displayName }}">
                    <iconify-icon icon="solar:trash-bin-minimalistic-linear" aria-hidden="true"></iconify-icon>
                </button>
            </form>
        @endif
    </div>
</article>
