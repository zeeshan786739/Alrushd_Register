@extends('admin.layouts.app')
@section('title', 'Edit Campaign')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Edit Campaign',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Campaigns','url'=>route('admin.email.campaigns.index')],['label'=>'Edit']],
    ])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <form method="POST" action="{{ route('admin.email.campaigns.update', $campaign) }}">
                @csrf @method('PUT')
                @include('admin.email-marketing.campaigns._form', ['campaign'=>$campaign,'templates'=>$templates])
                <div class="d-flex gap-12 mt-24">
                    <button class="btn btn-primary-600 radius-8">Update</button>
                    <a href="{{ route('admin.email.campaigns.show', $campaign) }}" class="btn btn-outline-neutral-500 radius-8">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
