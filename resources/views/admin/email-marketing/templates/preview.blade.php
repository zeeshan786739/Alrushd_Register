@extends('admin.layouts.app')
@section('title', 'Preview Template')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Preview: '.$template->name,'showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'Email Marketing'],['label'=>'Templates','url'=>route('admin.email.templates.index')],['label'=>'Preview']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24" style="overflow-wrap:anywhere">{!! $html !!}</div></div>
</div>
@endsection
