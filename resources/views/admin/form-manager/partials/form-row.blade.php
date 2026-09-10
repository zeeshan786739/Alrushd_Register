@php
    $icons = [
        'job-applications' => ['solar:case-round-linear', '#487FFF'],
        'staff-application' => ['solar:user-id-linear', '#16a34a'],
        'student-admission' => ['solar:square-academic-cap-linear', '#7c3aed'],
        'debit-form' => ['solar:wallet-linear', '#ca8a04'],
        'enquire-now' => ['solar:letter-linear', '#0891b2'],
        'referral' => ['solar:hand-shake-linear', '#db2777'],
        'meeting-form' => ['solar:calendar-linear', '#64748b'],
    ];
    [$icon, $color] = $icons[$form->slug] ?? ['solar:document-text-linear', '#487FFF'];
@endphp
<article class="fc-form-row fc-list-row"
         data-form-row
         data-form-id="{{ $form->id }}"
         data-form-name="{{ $form->name }}"
         data-is-active="{{ $form->is_active ? '1' : '0' }}"
         data-on-landing="{{ $form->hasPlacement('landing') ? '1' : '0' }}"
         data-entries-count="{{ $form->entries_count }}"
         data-entries-url="{{ route('admin.form-manager.entries', $form) }}"
         data-settings-url="{{ route('admin.form-manager.settings', $form) }}"
         data-toggle-url="{{ route('admin.form-manager.toggle', $form) }}"
         data-toggle-placement-url="{{ route('admin.form-manager.toggle-placement', $form) }}"
         data-placements="{{ implode(',', $form->placements()) }}"
         data-form-url="{{ $form->routePath() }}"
         data-destroy-url="{{ route('admin.form-manager.destroy', $form) }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="fc-list-row__identity">
        <span class="fc-form-icon" style="background: {{ $color }}18; color: {{ $color }};">
            <iconify-icon icon="{{ $icon }}"></iconify-icon>
        </span>
        <div class="fc-form-identity">
            <h6>{{ $form->name }}</h6>
            <span class="fc-table-url" title="/{{ $form->slug }}">/{{ $form->slug }}</span>
            @if($form->legacy_route && trim($form->legacy_route, '/') !== $form->slug)
                <span class="fc-table-url">{{ $form->legacy_route }}</span>
            @endif
        </div>
    </div>
    <div class="fc-list-row__field"><span class="fw-semibold">{{ $form->steps_count }}</span></div>
    <div class="fc-list-row__field"><span class="fw-semibold">{{ $form->fields_count }}</span></div>
    <div class="fc-list-row__field">
        <a href="{{ route('admin.form-manager.entries', $form) }}" class="fw-bold text-primary-600 hover-text-primary" onclick="event.stopPropagation()">
            {{ number_format($form->entries_count) }}
        </a>
    </div>
    <div class="fc-list-row__field" data-display-cell>
        @php $placements = $form->placements(); @endphp
        @if(empty($placements))
            <button type="button"
                    class="fc-badge fc-badge-neutral fc-badge-interactive border-0"
                    title="Open display settings"
                    data-form-settings>
                Not shown
            </button>
        @else
            <div class="d-flex flex-wrap gap-6">
                @foreach($placements as $placement)
                    @php $opt = $placementOptions[$placement] ?? null; @endphp
                    <button type="button"
                            class="fc-badge fc-badge-primary fc-badge-interactive border-0"
                            title="{{ $opt['description'] ?? $placement }} — click to toggle"
                            data-toggle-placement="{{ $placement }}">
                        {{ $opt['label'] ?? ucfirst($placement) }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>
    <div class="fc-list-row__field">
        <button type="button"
                class="fc-badge fc-badge-interactive border-0 {{ $form->is_active ? 'fc-badge-primary' : 'fc-badge-neutral' }}"
                data-toggle-status
                title="Click to toggle status">
            {{ $form->is_active ? 'Active' : 'Inactive' }}
        </button>
    </div>
    <div class="fc-list-row__actions">
        <div class="fc-table-actions">
            @if($form->usesDynamicRenderer())
            <a href="{{ $form->routePath() }}"
               target="_blank"
               rel="noopener"
               class="fc-action-icon view"
               title="Preview live form"
               aria-label="Preview live form">
                <x-form-action-icon name="view" />
            </a>
            @endif
            <a href="{{ route('admin.form-manager.edit', $form) }}"
               class="fc-action-icon edit"
               title="Customize form"
               aria-label="Customize form">
                <x-form-action-icon name="edit" />
            </a>
            <button type="button"
                    class="fc-action-icon settings border-0"
                    title="Display settings"
                    aria-label="Display settings"
                    data-form-settings
                    data-form-name="{{ $form->name }}"
                    data-settings-url="{{ route('admin.form-manager.settings', $form) }}"
                    data-placements="{{ implode(',', $form->placements()) }}">
                <x-form-action-icon name="settings" />
            </button>
            <button type="button"
                    class="fc-action-icon link border-0"
                    title="Copy form URL"
                    aria-label="Copy form URL"
                    data-copy-form-url="{{ $form->routePath() }}">
                <x-form-action-icon name="link" />
            </button>
            <form action="{{ route('admin.form-manager.duplicate', $form) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit"
                        class="fc-action-icon duplicate"
                        title="Duplicate form"
                        aria-label="Duplicate form">
                    <x-form-action-icon name="duplicate" />
                </button>
            </form>
            <button type="button"
                    class="fc-action-icon delete border-0"
                    title="Delete form"
                    aria-label="Delete form"
                    data-delete-form>
                <x-form-action-icon name="delete" />
            </button>
            <a href="{{ route('admin.form-manager.entries', $form) }}"
               class="btn btn-sm fc-submissions-btn radius-8 px-12 py-8 fc-btn"
               title="View submissions"
               aria-label="View submissions">
                <i class="ri-inbox-2-fill" aria-hidden="true"></i>
                <span>Submissions</span>
            </a>
        </div>
    </div>
</article>
