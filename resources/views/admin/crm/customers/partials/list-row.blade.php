@php
    $canUpdate = auth('admin')->user()?->can('update customers');
    $canDelete = auth('admin')->user()?->can('delete customers');
    $showUrl = route('admin.crm.customers.show', $customer);
@endphp
<article class="crm-list-row crm-customer-row crm-list-row--status-{{ $customer->status }}"
         data-crm-list-row
         data-crm-customer-open
         data-href="{{ $showUrl }}"
         data-customer-id="{{ $customer->id }}"
         data-current-status="{{ $customer->status }}"
         tabindex="0"
         role="button"
         aria-label="Open customer {{ $customer->name }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>

    <div class="crm-list-row__identity">
        <span class="crm-lead-avatar crm-lead-avatar--list" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($customer->name) }}</span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ $customer->name }}</span>
                <span class="crm-list-row__id">#{{ $customer->id }}</span>
            </div>
            @if($customer->company)
                <div class="crm-list-row__contact-line">
                    <span class="crm-list-row__contact" title="{{ $customer->company }}">
                        <iconify-icon icon="solar:buildings-2-linear" aria-hidden="true"></iconify-icon>
                        {{ Str::limit($customer->company, 32) }}
                    </span>
                </div>
            @endif
            <div class="crm-list-row__contact-line">
                @if($customer->email)
                    <span class="crm-list-row__contact" title="{{ $customer->email }}">
                        <iconify-icon icon="solar:letter-linear" aria-hidden="true"></iconify-icon>
                        {{ Str::limit($customer->email, 28) }}
                    </span>
                @endif
                @if($customer->phone)
                    <span class="crm-list-row__contact" title="{{ $customer->phone }}">
                        <iconify-icon icon="solar:phone-linear" aria-hidden="true"></iconify-icon>
                        {{ $customer->phone }}
                    </span>
                @endif
                @if(! $customer->email && ! $customer->phone)
                    <span class="crm-list-row__contact crm-list-row__contact--muted">No contact saved</span>
                @endif
            </div>
            @if($customer->source)
                <div class="crm-list-row__tags">
                    <span class="crm-list-row__form-name">{{ Str::limit($customer->source, 24) }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="crm-list-row__field crm-list-row__field--status">
        @if($canUpdate)
            @include('admin.crm.partials.inline-control', [
                'field' => 'status',
                'value' => $customer->status,
                'recordId' => $customer->id,
                'options' => $statusInlineOptions,
                'ariaLabel' => 'Status for '.$customer->name,
            ])
        @else
            @include('admin.crm.partials.status-pill', ['status' => $customer->status])
        @endif
    </div>

    <div class="crm-list-row__field crm-list-row__field--assignee">
        @if($canUpdate)
            @include('admin.crm.partials.inline-control', [
                'field' => 'assigned_to',
                'value' => $customer->assigned_to ?? '',
                'recordId' => $customer->id,
                'options' => $ownerInlineOptions,
                'owner' => true,
                'ariaLabel' => 'Owner for '.$customer->name,
            ])
        @else
            <span class="crm-list-row__assignee">
                <iconify-icon icon="solar:user-linear" aria-hidden="true"></iconify-icon>
                {{ $customer->assignedAdmin?->name ?? 'Unassigned' }}
            </span>
        @endif
    </div>

    <div class="crm-list-row__field crm-list-row__field--ltv">
        <span class="crm-list-row__ltv">{{ number_format((float) $customer->lifetime_value, 2) }}</span>
    </div>

    <div class="crm-list-row__field crm-list-row__field--date" title="{{ $customer->created_at->format('M j, Y g:i A') }}">
        <span class="crm-list-row__date">{{ $customer->created_at->diffForHumans(short: true) }}</span>
        <span class="crm-list-row__date-sub">{{ $customer->created_at->format('M j, Y') }}</span>
    </div>

    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canUpdate)
                <a href="{{ route('admin.crm.customers.edit', $customer) }}"
                   class="crm-list-action"
                   title="Edit customer"
                   aria-label="Edit {{ $customer->name }}"
                   onclick="event.stopPropagation()">
                    <iconify-icon icon="solar:pen-linear"></iconify-icon>
                </a>
            @endif
            @if($canDelete)
                <form action="{{ route('admin.crm.customers.destroy', $customer) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">
                    @csrf @method('DELETE')
                    <button type="submit" class="crm-list-action is-delete" title="Delete customer" aria-label="Delete {{ $customer->name }}">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                    </button>
                </form>
            @endif
        </div>
        <span class="crm-list-row__chevron" aria-hidden="true">
            <iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon>
        </span>
    </div>
</article>
