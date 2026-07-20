@extends('admin.layouts.app')
@section('title', 'Edit Template')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Edit Template','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'Email Marketing'],['label'=>'Templates','url'=>route('admin.email.templates.index')],['label'=>'Edit']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
        <form method="POST" action="{{ route('admin.email.templates.update', $template) }}">@csrf @method('PUT')
            @include('admin.email-marketing.templates._form', ['template'=>$template])
            <button class="btn btn-primary-600 radius-8 mt-24">Update</button>
        </form>
        <form method="POST" action="{{ route('admin.email.templates.duplicate', $template) }}" class="mt-12">@csrf
            <button class="btn btn-outline-neutral-500 radius-8">Duplicate</button>
        </form>
    </div></div>
</div>
@endsection
