@extends('platform.layouts.app')

@section('title', $plan->exists ? 'Edit Plan' : 'New Plan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <h6 class="fw-semibold mb-0">{{ $plan->exists ? 'Edit Plan — ' . $plan->name : 'Create a Plan' }}</h6>
    <a href="{{ route('platform.plans.index') }}" class="btn btn-outline-secondary">Back to Plans</a>
</div>

@if($errors->any())
<div class="alert alert-danger radius-8">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $plan->exists ? route('platform.plans.update', $plan) : route('platform.plans.store') }}">
    @csrf
    @if($plan->exists) @method('PUT') @endif

    <div class="row gy-4">
        <div class="col-lg-8">
            <div class="card radius-12 border-0 shadow-sm">
                <div class="card-body p-24 row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Plan name *</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $plan->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug *</label>
                        <input type="text" name="slug" class="form-control" required value="{{ old('slug', $plan->slug) }}" placeholder="starter">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $plan->tagline) }}"
                               placeholder="Perfect for single-campus schools">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description', $plan->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Features (one per line — shown on the pricing page)</label>
                        <textarea name="features_text" rows="7" class="form-control" placeholder="Unlimited leads&#10;Dynamic form builder&#10;Email marketing">{{ old('features_text', implode("\n", $plan->features ?? [])) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max admins (limit)</label>
                        <input type="number" name="max_admins" class="form-control" min="1"
                               value="{{ old('max_admins', $plan->limits['max_admins'] ?? '') }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max leads (limit)</label>
                        <input type="number" name="max_leads" class="form-control" min="1"
                               value="{{ old('max_leads', $plan->limits['max_leads'] ?? '') }}" placeholder="Unlimited">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card radius-12 border-0 shadow-sm">
                <div class="card-body p-24 row g-3">
                    <div class="col-7">
                        <label class="form-label">Price *</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price', $plan->price) }}">
                    </div>
                    <div class="col-5">
                        <label class="form-label">Currency *</label>
                        <select name="currency" class="form-select">
                            @foreach(['USD', 'GBP', 'EUR'] as $currency)
                                <option value="{{ $currency }}" @selected(old('currency', $plan->currency) === $currency)>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7">
                        <label class="form-label">Billing interval *</label>
                        <select name="billing_interval" class="form-select">
                            <option value="month" @selected(old('billing_interval', $plan->billing_interval) === 'month')>Monthly</option>
                            <option value="year" @selected(old('billing_interval', $plan->billing_interval) === 'year')>Yearly</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label">Trial days *</label>
                        <input type="number" name="trial_days" class="form-control" min="0" max="365" required value="{{ old('trial_days', $plan->trial_days) }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Sort order</label>
                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                    </div>
                    <div class="col-6 d-flex flex-column justify-content-end gap-2">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                   @checked(old('is_active', $plan->is_active))>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured"
                                   @checked(old('is_featured', $plan->is_featured))>
                            <label class="form-check-label" for="isFeatured">Featured</label>
                        </div>
                    </div>
                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary w-100 py-12">{{ $plan->exists ? 'Save Plan' : 'Create Plan' }}</button>
                    </div>
                    @if($plan->exists && $plan->isSyncedToStripe())
                    <div class="col-12">
                        <span class="text-secondary-light text-xs">Stripe price: <code>{{ $plan->stripe_price_id }}</code>. Re-sync after changing price/interval/currency.</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
