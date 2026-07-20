@extends('admin.layouts.app')
@section('title', 'Create Template')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Create Template','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'Email Marketing'],['label'=>'Templates','url'=>route('admin.email.templates.index')],['label'=>'Create']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
        <form method="POST" action="{{ route('admin.email.templates.store') }}">@csrf
            @include('admin.email-marketing.templates._form', ['template'=>null])
            <button class="btn btn-primary-600 radius-8 mt-24">Create</button>
        </form>
    </div></div>
</div>
@endsection
