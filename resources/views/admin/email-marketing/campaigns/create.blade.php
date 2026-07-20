@extends('admin.layouts.app')
@section('title', 'Create Campaign')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Create Campaign',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'Email Marketing'],['label'=>'Campaigns','url'=>route('admin.email.campaigns.index')],['label'=>'Create']],
    ])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <form method="POST" action="{{ route('admin.email.campaigns.store') }}">
                @csrf
                @include('admin.email-marketing.campaigns._form', ['campaign'=>null,'templates'=>$templates])
                <div class="d-flex gap-12 mt-24">
                    <button class="btn btn-primary-600 radius-8" name="send_now" value="0">Save Draft</button>
                    <button class="btn btn-outline-primary-600 radius-8" name="send_now" value="1">Save &amp; Send</button>
                    <a href="{{ route('admin.email.campaigns.index') }}" class="btn btn-outline-neutral-500 radius-8">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
