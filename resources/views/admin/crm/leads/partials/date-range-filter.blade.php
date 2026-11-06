@php
    use App\Support\LeadDateFilter;

    $dateField = LeadDateFilter::field(request());
    [$dateFrom, $dateTo] = LeadDateFilter::range(request());
    $dateSummary = LeadDateFilter::summary(request());
@endphp
<div class="crm-date-filter"
     data-crm-date-filter
     data-today="{{ today()->toDateString() }}"
     data-field="{{ $dateField }}"
     data-from="{{ $dateFrom?->toDateString() }}"
     data-to="{{ $dateTo?->toDateString() }}">
    <button type="button"
            @class(['crm-quick-pill', 'crm-date-filter__trigger', 'is-active' => $dateSummary !== null])
            data-crm-date-trigger
            aria-haspopup="dialog"
            aria-expanded="false">
        <iconify-icon icon="solar:calendar-linear" aria-hidden="true"></iconify-icon>
        {{ $dateSummary ?? 'Any date' }}
        <iconify-icon class="crm-date-filter__chevron" icon="solar:alt-arrow-down-linear" aria-hidden="true"></iconify-icon>
    </button>
    <div class="crm-date-filter__panel" data-crm-date-panel role="dialog" aria-label="Filter leads by date" hidden>
        <div class="crm-date-filter__fields" role="group" aria-label="Which date to filter by">
            @foreach(LeadDateFilter::FIELDS as $value => $label)
                <button type="button" class="crm-date-filter__field" data-crm-date-field="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="crm-date-filter__body">
            <div class="crm-date-filter__presets" data-crm-date-presets></div>
            <div class="crm-date-filter__calendar">
                <button type="button" class="crm-date-filter__nav crm-date-filter__nav--prev" data-crm-date-nav="-1" aria-label="Previous month">
                    <iconify-icon icon="solar:alt-arrow-left-linear" aria-hidden="true"></iconify-icon>
                </button>
                <button type="button" class="crm-date-filter__nav crm-date-filter__nav--next" data-crm-date-nav="1" aria-label="Next month">
                    <iconify-icon icon="solar:alt-arrow-right-linear" aria-hidden="true"></iconify-icon>
                </button>
                <div class="crm-date-filter__months" data-crm-date-months></div>
            </div>
        </div>
        <div class="crm-date-filter__footer">
            <label class="crm-date-filter__input"><span>From</span><input type="date" class="form-control" data-crm-date-from></label>
            <label class="crm-date-filter__input"><span>To</span><input type="date" class="form-control" data-crm-date-to></label>
            <div class="crm-date-filter__actions">
                <button type="button" class="crm-filter-workspace__btn" data-crm-date-clear>Clear</button>
                <button type="button" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary" data-crm-date-apply>Apply</button>
            </div>
        </div>
    </div>
</div>
