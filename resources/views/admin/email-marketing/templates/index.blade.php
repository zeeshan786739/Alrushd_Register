@extends('admin.layouts.app')
@section('title', 'Templates')
@section('content')
@include('admin.email-marketing.partials.shell', [
    'activeTab' => 'templates',
    'shellTitle' => 'Email templates',
    'shellSubtitle' => 'Reusable designs for campaigns — save time and keep your brand consistent.',
    'shellActions' => array_values(array_filter([
        auth('admin')->user()?->can('create templates') ? [
            'label' => 'New template',
            'url' => route('admin.email.templates.create'),
            'class' => 'btn-primary-600 radius-8 px-20 py-11',
            'icon' => 'solar:add-circle-linear',
        ] : null,
    ])),
])

<div class="em-panel">
    <div class="em-panel__head">
        <div>
            <h2 class="em-panel__title">Saved templates</h2>
            <p class="em-panel__desc">Use templates when creating campaigns for faster setup.</p>
        </div>
    </div>

    @if($templates->isEmpty())
        <div class="em-empty-state">
            <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
            <h3>No templates yet</h3>
            <p>Create a reusable email design for open days, reminders, and newsletters.</p>
            @can('create templates')
            <a href="{{ route('admin.email.templates.create') }}" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn">
                <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Create template
            </a>
            @endcan
        </div>
    @else
        <div class="table-responsive">
            <table class="table mb-0 align-middle em-table">
                <thead>
                    <tr>
                        <th class="ps-24">Name</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Active</th>
                        <th class="pe-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                    <tr>
                        <td class="ps-24 fw-semibold">{{ $template->name }}</td>
                        <td>{{ $template->subject ?: '—' }}</td>
                        <td>{{ $template->category ?: '—' }}</td>
                        <td>
                            @if($template->is_active)
                                <span class="em-status-pill em-status-pill--sent">Active</span>
                            @else
                                <span class="em-status-pill em-status-pill--draft">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-24 text-end">
                            <div class="d-flex gap-8 justify-content-end">
                                <a href="{{ route('admin.email.templates.preview', $template) }}" class="btn btn-sm btn-outline-neutral-500 radius-8">Preview</a>
                                @can('update templates')
                                <a href="{{ route('admin.email.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Edit</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-20">{{ $templates->links() }}</div>
    @endif
</div>
@endsection
