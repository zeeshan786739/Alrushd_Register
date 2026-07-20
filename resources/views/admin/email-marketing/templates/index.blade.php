@extends('admin.layouts.app')
@section('title', 'Templates')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Email Templates',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Templates']],
        'actions' => array_filter([
            auth('admin')->user()?->can('create templates')
                ? ['label'=>'New Template','url'=>route('admin.email.templates.create'),'class'=>'btn-primary-600 radius-8 px-20 py-11']
                : null,
        ]),
    ])
    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Name</th><th>Subject</th><th>Category</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->subject }}</td>
                            <td>{{ $template->category ?: '—' }}</td>
                            <td>{{ $template->is_active ? 'Yes' : 'No' }}</td>
                            <td class="d-flex gap-8">
                                <a href="{{ route('admin.email.templates.preview', $template) }}" class="btn btn-sm btn-outline-neutral-500 radius-8">Preview</a>
                                @can('update templates')
                                <a href="{{ route('admin.email.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-40 text-secondary-light">No templates yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-16">{{ $templates->links() }}</div>
</div>
@endsection
