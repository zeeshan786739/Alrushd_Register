@include('admin.partials.page-header', [
    'title' => $title,
    'subtitle' => $subtitle ?? null,
    'breadcrumbs' => $breadcrumbs ?? [],
])

@php
    $addAction = ($actions ?? [])[0] ?? null;
    $addUrl = $addUrl ?? ($addAction['url'] ?? null);
@endphp

<div class="col-12">
    <div class="card basic-data-table">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title text-primary mb-0">{{ $listTitle ?? $title }} List</h6>
            @if($addUrl)
                <a href="{{ $addUrl }}" class="btn btn-primary btn-sm">+ Add</a>
            @endif
        </div>
        <div class="card-body">
            <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
                <thead>
                    <tr>
                        <th scope="col">
                            <div class="form-check style-check d-flex align-items-center">
                                <label class="form-check-label">S.L</label>
                            </div>
                        </th>
                        <th scope="col">Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr>
                        <td>
                            <div class="form-check style-check d-flex align-items-center">
                                <label class="form-check-label">{{ $loop->iteration }}</label>
                            </div>
                        </td>
                        <td>{{ $item->name }}</td>
                        <td>
                            @include('admin.partials.status-badge', ['status' => $item->status])
                        </td>
                        <td>
                            @include('admin.partials.table-actions', [
                                'viewUrl' => route($routePrefix . '.edit', $item->id),
                                'editUrl' => route($routePrefix . '.edit', $item->id),
                                'deleteId' => $item->id,
                                'deleteRoute' => route($routePrefix . '.destroy', $item->id),
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
