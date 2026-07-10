@extends('admin.layouts.app')

@section('title') Form Center @endsection

@section('content')
@include('admin.form-manager.partials.styles')

<div class="dashboard-main-body">
    @include('admin.form-manager.partials.header', [
        'title' => 'Form Center',
        'subtitle' => 'Create, customize, and manage every form and submission from one place.',
        'actions' => [
            ['label' => 'Create New Form', 'url' => route('admin.form-manager.create'), 'class' => 'btn-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:add-circle-linear'],
        ],
    ])

    <div class="row gy-4 mb-24" id="formStatFilters">
        <div class="col-xxl-3 col-sm-6">
            <button type="button"
                    class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-1 fc-stat-card fc-stat-filter w-100 text-start is-active"
                    data-stat-filter="all"
                    aria-pressed="true">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-primary-600 text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:document-text-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">Total Forms</span>
                        <h6 class="fw-bold mb-0 text-lg" data-stat-count="total">{{ $stats['total_forms'] }}</h6>
                    </div>
                </div>
            </button>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <button type="button"
                    class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-2 fc-stat-card fc-stat-filter w-100 text-start"
                    data-stat-filter="active"
                    aria-pressed="false">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-success-main text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:check-circle-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">Active Forms</span>
                        <h6 class="fw-bold mb-0 text-lg" data-stat-count="active">{{ $stats['active_forms'] }}</h6>
                    </div>
                </div>
            </button>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <button type="button"
                    class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-3 fc-stat-card fc-stat-filter w-100 text-start"
                    data-stat-filter="landing"
                    aria-pressed="false">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-yellow text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:global-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">On Landing Page</span>
                        <h6 class="fw-bold mb-0 text-lg" data-stat-count="landing">{{ $stats['landing_forms'] }}</h6>
                    </div>
                </div>
            </button>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <button type="button"
                    class="card p-3 shadow-2 radius-12 border-0 h-100 bg-gradient-end-4 fc-stat-card fc-stat-filter w-100 text-start"
                    data-stat-filter="submissions"
                    aria-pressed="false">
                <div class="card-body p-0 d-flex align-items-center gap-3">
                    <span class="w-48-px h-48-px bg-purple text-white d-flex justify-content-center align-items-center rounded-circle">
                        <iconify-icon icon="solar:inbox-linear" class="text-xl"></iconify-icon>
                    </span>
                    <div>
                        <span class="fw-medium text-secondary-light text-sm d-block mb-4">With Submissions</span>
                        <h6 class="fw-bold mb-0 text-lg" data-stat-count="submissions">{{ $forms->where('entries_count', '>', 0)->count() }}</h6>
                        <span class="text-secondary-light text-xs">{{ number_format($stats['total_submissions']) }} total entries</span>
                    </div>
                </div>
            </button>
        </div>
    </div>

    @if($forms->isEmpty())
    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body text-center py-60 px-24">
            <div class="w-72-px h-72-px bg-primary-50 text-primary-600 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-16">
                <iconify-icon icon="solar:document-add-linear" class="text-3xl"></iconify-icon>
            </div>
            <h6 class="fw-semibold mb-8">Form Center is not set up yet</h6>
            <p class="text-secondary-light text-sm mb-20 max-w-500-px mx-auto">Run the setup command to import all existing forms and submission data into the unified system.</p>
            <code class="d-inline-block bg-neutral-100 px-16 py-10 radius-8 text-sm mb-24">php artisan forms:setup</code>
            <div>
                <a href="{{ route('admin.form-manager.create') }}" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                    <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                    <span>Create your first form</span>
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <div>
                    <h6 class="mb-4 fw-semibold fc-panel-title">
                        <iconify-icon icon="solar:documents-linear"></iconify-icon>
                        <span id="formTableTitle">All Forms</span>
                    </h6>
                    <p class="text-secondary-light text-sm mb-0">
                        <span id="formTableSubtitle">Showing all forms</span>
                        <span class="text-primary-600 fw-medium" id="formTableCount"></span>
                    </p>
                </div>
                <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 text-sm d-none" id="clearFormFilter">
                    Clear filter
                </button>
            </div>
            <div class="table-responsive">
                <table class="table bordered-table mb-0 align-middle" id="formsTable">
                    <thead>
                        <tr>
                            <th class="ps-24">Form</th>
                            <th>Steps</th>
                            <th>Fields</th>
                            <th>Submissions</th>
                            <th>Display</th>
                            <th>Status</th>
                            <th class="text-end pe-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($forms as $form)
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
                        <tr class="fc-form-row"
                            data-form-row
                            data-form-id="{{ $form->id }}"
                            data-form-name="{{ $form->name }}"
                            data-is-active="{{ $form->is_active ? '1' : '0' }}"
                            data-on-landing="{{ $form->hasPlacement('landing') ? '1' : '0' }}"
                            data-entries-count="{{ $form->entries_count }}"
                            data-settings-url="{{ route('admin.form-manager.settings', $form) }}"
                            data-toggle-url="{{ route('admin.form-manager.toggle', $form) }}"
                            data-toggle-placement-url="{{ route('admin.form-manager.toggle-placement', $form) }}"
                            data-placements="{{ implode(',', $form->placements()) }}"
                            data-form-url="{{ url($form->routePath()) }}"
                            data-destroy-url="{{ route('admin.form-manager.destroy', $form) }}">
                            <td class="ps-24">
                                <div class="d-flex align-items-center gap-12">
                                    <span class="fc-form-icon" style="background: {{ $color }}18; color: {{ $color }};">
                                        <iconify-icon icon="{{ $icon }}"></iconify-icon>
                                    </span>
                                    <div>
                                        <h6 class="text-md fw-semibold mb-4">{{ $form->name }}</h6>
                                        <span class="text-secondary-light text-sm">/{{ $form->slug }}</span>
                                        @if($form->legacy_route)
                                        <span class="text-secondary-light text-xs d-block mt-2">{{ $form->legacy_route }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="fw-semibold">{{ $form->steps_count }}</span></td>
                            <td><span class="fw-semibold">{{ $form->fields_count }}</span></td>
                            <td>
                                <a href="{{ route('admin.form-manager.entries', $form) }}" class="fw-bold text-primary-600 hover-text-primary">
                                    {{ number_format($form->entries_count) }}
                                </a>
                            </td>
                            <td data-display-cell>
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
                            </td>
                            <td>
                                <button type="button"
                                        class="fc-badge fc-badge-interactive border-0 {{ $form->is_active ? 'fc-badge-primary' : 'fc-badge-neutral' }}"
                                        data-toggle-status
                                        title="Click to toggle status">
                                    {{ $form->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="text-end pe-24">
                                <div class="fc-table-actions">
                                    <a href="{{ route('admin.form-manager.edit', $form) }}"
                                       class="fc-action-icon edit"
                                       title="Customize form"
                                       aria-label="Customize form">
                                        <iconify-icon icon="solar:pen-linear"></iconify-icon>
                                    </a>
                                    <button type="button"
                                            class="fc-action-icon view border-0"
                                            title="Display settings"
                                            aria-label="Display settings"
                                            data-form-settings
                                            data-form-name="{{ $form->name }}"
                                            data-settings-url="{{ route('admin.form-manager.settings', $form) }}"
                                            data-placements="{{ implode(',', $form->placements()) }}">
                                        <iconify-icon icon="solar:settings-linear"></iconify-icon>
                                    </button>
                                    <a href="{{ route('admin.form-manager.entries', $form) }}"
                                       class="fc-action-icon view"
                                       title="View submissions"
                                       aria-label="View submissions">
                                        <iconify-icon icon="solar:inbox-linear"></iconify-icon>
                                    </a>
                                    <button type="button"
                                            class="fc-action-icon link border-0"
                                            title="Copy form URL"
                                            aria-label="Copy form URL"
                                            data-copy-form-url="{{ url($form->routePath()) }}">
                                        <iconify-icon icon="solar:link-round-linear"></iconify-icon>
                                    </button>
                                    <form action="{{ route('admin.form-manager.duplicate', $form) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="fc-action-icon duplicate"
                                                title="Duplicate form"
                                                aria-label="Duplicate form">
                                            <iconify-icon icon="solar:copy-linear"></iconify-icon>
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="fc-action-icon delete border-0"
                                            title="Delete form"
                                            aria-label="Delete form"
                                            data-delete-form>
                                        <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-none px-24 py-40 text-center" id="formFilterEmpty">
                    <iconify-icon icon="solar:filter-linear" class="text-3xl text-secondary-light mb-12 d-block"></iconify-icon>
                    <h6 class="fw-semibold mb-8">No forms match this filter</h6>
                    <p class="text-secondary-light text-sm mb-0">Try another stat card or clear the filter.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('modals')
    @include('admin.form-manager.partials.settings-modal', ['placementOptions' => $placementOptions])
@endsection

@section('script')
<script>
(function () {
    const csrfToken = @json(csrf_token());
    const placementOptions = @json($placementOptions);

    function showToast(message, icon) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon || 'success',
            title: message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload || {}),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong.');
        }
        return data;
    }

    async function putJson(url, payload) {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload || {}),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong.');
        }
        return data;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderDisplayCell(row, placements) {
        const cell = row.querySelector('[data-display-cell]');
        if (!cell) return;

        if (!placements.length) {
            cell.innerHTML = '<button type="button" class="fc-badge fc-badge-neutral fc-badge-interactive border-0" title="Open display settings" data-form-settings>Not shown</button>';
            return;
        }

        cell.innerHTML = '<div class="d-flex flex-wrap gap-6">' + placements.map(function (placement) {
            const opt = placementOptions[placement] || {};
            const label = escapeHtml(opt.label || placement.charAt(0).toUpperCase() + placement.slice(1));
            const title = escapeHtml((opt.description || placement) + ' — click to toggle');
            return '<button type="button" class="fc-badge fc-badge-primary fc-badge-interactive border-0" title="' + title + '" data-toggle-placement="' + escapeHtml(placement) + '">' + label + '</button>';
        }).join('') + '</div>';
    }

    function updateRowPlacements(row, placements) {
        row.dataset.placements = placements.join(',');
        renderDisplayCell(row, placements);

        const settingsBtn = row.querySelector('[data-form-settings]');
        if (settingsBtn) {
            settingsBtn.dataset.placements = placements.join(',');
        }
    }

    function updateStatusButton(button, isActive) {
        button.textContent = isActive ? 'Active' : 'Inactive';
        button.classList.toggle('fc-badge-primary', isActive);
        button.classList.toggle('fc-badge-neutral', !isActive);

        const row = button.closest('[data-form-row]');
        if (row) {
            row.dataset.isActive = isActive ? '1' : '0';
            applyFormFilter(currentFormFilter);
        }
    }

    const filterMeta = {
        all: { title: 'All Forms', subtitle: 'Showing all forms' },
        active: { title: 'Active Forms', subtitle: 'Showing active forms only' },
        landing: { title: 'On Landing Page', subtitle: 'Showing forms on the landing page' },
        submissions: { title: 'With Submissions', subtitle: 'Showing forms that have submissions' },
    };

    let currentFormFilter = 'all';

    function applyFormFilter(filter) {
        currentFormFilter = filter;
        const rows = Array.from(document.querySelectorAll('[data-form-row]'));
        const table = document.getElementById('formsTable');
        const emptyState = document.getElementById('formFilterEmpty');
        const clearBtn = document.getElementById('clearFormFilter');
        const titleEl = document.getElementById('formTableTitle');
        const subtitleEl = document.getElementById('formTableSubtitle');
        const countEl = document.getElementById('formTableCount');
        let visibleCount = 0;

        rows.forEach(function (row) {
            const isActive = row.dataset.isActive === '1';
            const onLanding = row.dataset.onLanding === '1';
            const entriesCount = parseInt(row.dataset.entriesCount || '0', 10);
            let visible = true;

            if (filter === 'active') visible = isActive;
            else if (filter === 'landing') visible = onLanding;
            else if (filter === 'submissions') visible = entriesCount > 0;

            row.classList.toggle('d-none', !visible);
            if (visible) visibleCount += 1;
        });

        if (filter === 'submissions') {
            rows
                .filter(function (row) { return !row.classList.contains('d-none'); })
                .sort(function (a, b) {
                    return parseInt(b.dataset.entriesCount || '0', 10) - parseInt(a.dataset.entriesCount || '0', 10);
                })
                .forEach(function (row) {
                    row.parentElement?.appendChild(row);
                });
        }

        document.querySelectorAll('[data-stat-filter]').forEach(function (card) {
            const isSelected = card.dataset.statFilter === filter;
            card.classList.toggle('is-active', isSelected);
            card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });

        if (titleEl && filterMeta[filter]) titleEl.textContent = filterMeta[filter].title;
        if (subtitleEl && filterMeta[filter]) subtitleEl.textContent = filterMeta[filter].subtitle;
        if (countEl) countEl.textContent = visibleCount ? ' · ' + visibleCount + ' shown' : '';
        if (clearBtn) clearBtn.classList.toggle('d-none', filter === 'all');
        if (table) table.classList.toggle('d-none', visibleCount === 0);
        if (emptyState) emptyState.classList.toggle('d-none', visibleCount > 0);
    }

    document.querySelectorAll('[data-stat-filter]').forEach(function (card) {
        card.addEventListener('click', function () {
            const filter = card.dataset.statFilter || 'all';
            applyFormFilter(filter === currentFormFilter && filter !== 'all' ? 'all' : filter);
        });
    });

    document.getElementById('clearFormFilter')?.addEventListener('click', function () {
        applyFormFilter('all');
    });

    applyFormFilter('all');

    const modalEl = document.getElementById('formSettingsModal');
    const form = document.getElementById('formSettingsForm');
    const subtitle = document.getElementById('formSettingsSubtitle');
    let activeSettingsRow = null;
    let modal = null;

    if (modalEl && form && subtitle && typeof bootstrap !== 'undefined') {
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        modal = bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: true,
            keyboard: true,
            focus: true,
        });

        function syncOptionStates() {
            modalEl.querySelectorAll('[data-settings-option]').forEach(function (option) {
                const input = option.querySelector('[data-placement]');
                option.classList.toggle('is-selected', Boolean(input && input.checked));
            });
        }

        modalEl.addEventListener('change', function (event) {
            if (event.target.matches('[data-placement]')) {
                syncOptionStates();
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!activeSettingsRow) return;

            const submitBtn = form.querySelector('[type="submit"]');
            submitBtn?.classList.add('is-loading');
            submitBtn?.setAttribute('disabled', 'disabled');

            const placements = Array.from(form.querySelectorAll('[data-placement]:checked')).map(function (input) {
                return input.value;
            });

            try {
                const data = await putJson(form.action, { placements: placements });
                updateRowPlacements(activeSettingsRow, data.placements || []);
                activeSettingsRow.dataset.onLanding = (data.placements || []).includes('landing') ? '1' : '0';
                applyFormFilter(currentFormFilter);
                modal.hide();
                showToast(data.message || 'Display settings saved.');
            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                submitBtn?.classList.remove('is-loading');
                submitBtn?.removeAttribute('disabled');
            }
        });
    }

    function openSettingsModal(row) {
        if (!modal || !form || !subtitle) return;

        activeSettingsRow = row;
        const placements = (row.dataset.placements || '').split(',').filter(Boolean);

        form.action = row.dataset.settingsUrl || '';
        subtitle.textContent = 'Choose where "' + (row.dataset.formName || 'Form') + '" appears on the website.';

        modalEl.querySelectorAll('[data-placement]').forEach(function (input) {
            input.checked = placements.includes(input.dataset.placement);
        });

        modalEl.querySelectorAll('[data-settings-option]').forEach(function (option) {
            const input = option.querySelector('[data-placement]');
            option.classList.toggle('is-selected', Boolean(input && input.checked));
        });

        modal.show();
    }

    document.addEventListener('click', async function (event) {
        const settingsBtn = event.target.closest('[data-form-settings]');
        if (settingsBtn) {
            event.preventDefault();
            const row = settingsBtn.closest('[data-form-row]');
            if (row) openSettingsModal(row);
            return;
        }

        const statusBtn = event.target.closest('[data-toggle-status]');
        if (statusBtn) {
            event.preventDefault();
            const row = statusBtn.closest('[data-form-row]');
            if (!row) return;

            statusBtn.classList.add('is-loading');
            try {
                const data = await postJson(row.dataset.toggleUrl);
                updateStatusButton(statusBtn, data.is_active);
                showToast(data.message || 'Status updated.');
            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                statusBtn.classList.remove('is-loading');
            }
            return;
        }

        const placementBtn = event.target.closest('[data-toggle-placement]');
        if (placementBtn) {
            event.preventDefault();
            const row = placementBtn.closest('[data-form-row]');
            if (!row) return;

            placementBtn.classList.add('is-loading');
            try {
                const data = await postJson(row.dataset.togglePlacementUrl, {
                    placement: placementBtn.dataset.togglePlacement,
                });
                updateRowPlacements(row, data.placements || []);
                row.dataset.onLanding = (data.placements || []).includes('landing') ? '1' : '0';
                applyFormFilter(currentFormFilter);
                showToast(data.message || 'Display updated.');
            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                placementBtn.classList.remove('is-loading');
            }
            return;
        }

        const copyUrlBtn = event.target.closest('[data-copy-form-url]');
        if (copyUrlBtn) {
            event.preventDefault();
            const url = copyUrlBtn.dataset.copyFormUrl || '';
            if (!url) return;

            const copyText = async function () {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(url);
                    return;
                }
                const input = document.createElement('textarea');
                input.value = url;
                input.setAttribute('readonly', '');
                input.style.position = 'absolute';
                input.style.left = '-9999px';
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            };

            try {
                await copyText();
                showToast('Form URL copied to clipboard.');
            } catch (error) {
                showToast('Could not copy URL. Please copy manually: ' + url, 'error');
            }
            return;
        }

        const deleteBtn = event.target.closest('[data-delete-form]');
        if (deleteBtn) {
            event.preventDefault();
            const row = deleteBtn.closest('[data-form-row]');
            if (!row) return;

            const formName = row.dataset.formName || 'this form';
            const destroyUrl = row.dataset.destroyUrl;
            if (!destroyUrl) return;

            const runDelete = async function () {
                deleteBtn.classList.add('is-loading');
                try {
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('_token', csrfToken);

                    const response = await fetch(destroyUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || 'Could not delete form.');
                    }

                    row.remove();
                    applyFormFilter(currentFormFilter);
                    showToast(data.message || 'Form deleted.');
                } catch (error) {
                    showToast(error.message, 'error');
                } finally {
                    deleteBtn.classList.remove('is-loading');
                }
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete form?',
                    html: 'This will permanently remove <strong>' + escapeHtml(formName) + '</strong> and all its submissions.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (result.isConfirmed) runDelete();
                });
            } else if (confirm('Delete "' + formName + '"? This cannot be undone.')) {
                runDelete();
            }
        }
    });
})();
</script>
@endsection
