@extends('admin.layouts.app')

@section('title') Form Center @endsection

@section('content')
@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.form-manager.partials.premium-styles')
@endonce

<div class="dashboard-main-body" id="form-center-page">
    @include('admin.partials.page-header', [
        'title' => 'Form Center',
        'subtitle' => 'Create, customize, and manage every form and submission from one place.',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'Forms & Intake'], ['label' => 'Form Center']],
        'actions' => [
            ['label' => 'Create New Form', 'url' => route('admin.form-manager.create'), 'class' => 'btn-primary-600 radius-8 px-20 py-11', 'icon' => 'solar:add-circle-linear'],
        ],
    ])

    <div class="fc-workspace crm-workspace-shell">
        <div class="crm-metrics-strip" aria-label="Form Center metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">Form Center workspace</span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Total</span><strong>{{ $stats['total_forms'] }}</strong></span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Active</span><strong>{{ $stats['active_forms'] }}</strong></span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item"><span class="crm-metrics-strip__label">Submissions</span><strong>{{ number_format($stats['total_submissions']) }}</strong></span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
                <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="fc-filter-workspace">
            <div class="fc-filter-workspace__head">
                <h2 class="fc-filter-workspace__title">Form filters</h2>
                <p class="fc-filter-workspace__sub">Quickly narrow the list by status, placement, or activity</p>
            </div>
            <div class="fc-source-grid" id="formStatFilters">
                <button type="button"
                        class="fc-stat-filter fc-stat-filter--all is-active"
                        data-stat-filter="all"
                        aria-pressed="true">
                    <span class="fc-stat-filter__icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></span>
                    <span class="fc-stat-filter__label">Total Forms</span>
                    <strong class="fc-stat-filter__count" data-stat-count="total">{{ $stats['total_forms'] }}</strong>
                </button>
                <button type="button"
                        class="fc-stat-filter fc-stat-filter--active"
                        data-stat-filter="active"
                        aria-pressed="false">
                    <span class="fc-stat-filter__icon"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></span>
                    <span class="fc-stat-filter__label">Active Forms</span>
                    <strong class="fc-stat-filter__count" data-stat-count="active">{{ $stats['active_forms'] }}</strong>
                </button>
                <button type="button"
                        class="fc-stat-filter fc-stat-filter--landing"
                        data-stat-filter="landing"
                        aria-pressed="false">
                    <span class="fc-stat-filter__icon"><iconify-icon icon="solar:global-linear"></iconify-icon></span>
                    <span class="fc-stat-filter__label">On Landing Page</span>
                    <strong class="fc-stat-filter__count" data-stat-count="landing">{{ $stats['landing_forms'] }}</strong>
                </button>
                <button type="button"
                        class="fc-stat-filter fc-stat-filter--submissions"
                        data-stat-filter="submissions"
                        aria-pressed="false">
                    <span class="fc-stat-filter__icon"><iconify-icon icon="solar:inbox-linear"></iconify-icon></span>
                    <span class="fc-stat-filter__label">With Submissions</span>
                    <strong class="fc-stat-filter__count" data-stat-count="submissions">{{ $forms->where('entries_count', '>', 0)->count() }}</strong>
                    <span class="fc-stat-filter__meta">{{ number_format($stats['total_submissions']) }} total entries</span>
                </button>
            </div>
        </div>
    </div>

    <div class="fc-page-body">
        @if($forms->isEmpty())
            <div class="crm-leads-table">
                <div class="crm-leads-list-empty">
                    <iconify-icon icon="solar:document-add-linear"></iconify-icon>
                    <strong>No forms yet</strong>
                    <span>Create your first form to collect enquiries, applications, or registrations. You can publish it on your website or share a direct link.</span>
                    <a href="{{ route('admin.form-manager.create') }}" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn mt-12">
                        <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                        <span>Create your first form</span>
                    </a>
                </div>
            </div>
        @else
            <div class="crm-leads-toolbar">
                <div class="crm-leads-toolbar__meta">
                    <strong id="formTableTitle">All Forms</strong>
                    <span id="formTableSubtitle">Showing all forms</span>
                    <span class="text-primary-600 fw-medium" id="formTableCount"></span>
                </div>
                <button type="button" class="btn btn-outline-neutral-500 radius-8 px-16 py-10 text-sm d-none" id="clearFormFilter">
                    Clear filter
                </button>
            </div>

            <div class="crm-list-shell">
                <div class="crm-leads-table">
                    <div class="crm-leads-table__head" aria-hidden="true">
                        <span>Form</span><span>Steps</span><span>Fields</span><span>Submissions</span><span>Display</span><span>Status</span><span></span>
                    </div>
                    <div class="crm-leads-list" id="formsTable">
                        @foreach($forms as $form)
                            @include('admin.form-manager.partials.form-row', compact('form', 'placementOptions'))
                        @endforeach
                    </div>
                    <div class="d-none px-24 py-40 text-center crm-leads-list-empty" id="formFilterEmpty">
                        <iconify-icon icon="solar:filter-linear"></iconify-icon>
                        <strong>No forms match this filter</strong>
                        <span>Try another stat card or clear the filter.</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
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
        const list = document.getElementById('formsTable');
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
        if (list) list.classList.toggle('d-none', visibleCount === 0);
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

    document.getElementById('formsTable')?.addEventListener('click', function (event) {
        const row = event.target.closest('[data-form-row]');
        if (!row || !row.dataset.entriesUrl) return;
        if (event.target.closest('.fc-table-actions')) return;
        if (event.target.closest('a, button, form, input, select, textarea, label')) return;

        window.location.href = row.dataset.entriesUrl;
    });

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

    function confirmDeleteForm(formName) {
        const note = 'This permanently removes the form and all of its submissions. This cannot be undone.';
        if (window.CrmUI && typeof window.CrmUI.confirm === 'function') {
            return window.CrmUI.confirm({
                title: 'Delete form?',
                message: 'Delete “' + formName + '”?',
                note: note,
                label: 'Delete form',
                tone: 'danger',
                icon: 'solar:trash-bin-minimalistic-linear',
            });
        }
        return Promise.resolve(window.confirm('Delete "' + formName + '"?\n\n' + note));
    }

    const formCenterPage = document.getElementById('form-center-page');
    (formCenterPage || document).addEventListener('click', async function (event) {
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

            confirmDeleteForm(formName).then(function (confirmed) {
                if (confirmed) runDelete();
            });
        }
    });
})();
</script>
@endsection
