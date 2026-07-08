    @include('admin.partials.page-header', [
        'title' => $title,
        'subtitle' => $subtitle ?? null,
        'breadcrumbs' => $breadcrumbs ?? [],
        'actions' => $actions ?? [],
    ])

    <div class="card shadow-2 radius-12 border-0">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 px-24 py-16 border-bottom">
                <h6 class="mb-0 fw-semibold fc-panel-title">
                    @if(!empty($icon))
                        <iconify-icon icon="{{ $icon }}"></iconify-icon>
                    @endif
                    {{ $listTitle ?? $title }}
                    @if(isset($data) && method_exists($data, 'count'))
                        <span class="fc-badge fc-badge-neutral ms-8">{{ $data->count() }}</span>
                    @elseif(isset($data))
                        <span class="fc-badge fc-badge-neutral ms-8">{{ count($data) }}</span>
                    @endif
                </h6>
                @include('admin.partials.search-bar', ['placeholder' => $searchPlaceholder ?? 'Search…'])
            </div>

            @if(empty($data) || (is_countable($data) && count($data) === 0))
                <div class="um-empty-state">
                    <iconify-icon icon="{{ $emptyIcon ?? 'solar:document-linear' }}" class="d-block mx-auto"></iconify-icon>
                    <h6 class="fw-semibold mb-8">{{ $emptyTitle ?? 'No records yet' }}</h6>
                    <p class="text-secondary-light text-sm mb-0">{{ $emptyText ?? 'Add your first entry to get started.' }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0 align-middle" id="dataTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th class="ps-24" style="width:60px">#</th>
                                <th>Name</th>
                                <th style="width:120px">Status</th>
                                <th class="text-end pe-24" style="width:120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                            <tr class="fc-form-row">
                                <td class="ps-24 fw-semibold text-secondary-light">{{ $loop->iteration }}</td>
                                <td><span class="fw-medium">{{ $item->name }}</span></td>
                                <td>
                                    @include('admin.partials.status-badge', ['status' => $item->status])
                                </td>
                                <td class="text-end pe-24">
                                    @include('admin.partials.table-actions', [
                                        'editUrl' => route($routePrefix . '.edit', $item->id),
                                        'deleteId' => $item->id,
                                        'deleteRoute' => route($routePrefix . '.destroy', $item->id),
                                        'canView' => false,
                                    ])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
