@extends('platform.layouts.app')

@section('title', $plan->exists ? 'Edit Plan' : 'New Plan')

@php
    $selectedModules = old('modules', $plan->modules ?? \App\Support\PlanEntitlements::allModuleKeys());
    $moduleMarketing = \App\Support\PlanEntitlements::marketingLines($selectedModules);
    $storedFeatures = $plan->features ?? [];
    $extraFeatures = old('extra_features', array_values(array_diff($storedFeatures, $moduleMarketing)));
    if ($extraFeatures === [] || $extraFeatures === ['']) {
        $extraFeatures = [''];
    }
    $selectedInterval = old('billing_interval', $plan->billing_interval ?? 'month');
    $modulesByGroup = [];
    foreach ($moduleCatalog as $moduleKey => $definition) {
        $group = $definition['group'] ?? 'Other';
        $modulesByGroup[$group][$moduleKey] = $definition;
    }
@endphp

@section('content')
<div class="plan-editor">
    <div class="plan-editor__top">
        <div>
            <a href="{{ route('platform.plans.index') }}" class="plan-editor__back">
                <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Plans
            </a>
            <h1 class="plan-editor__title">{{ $plan->exists ? 'Edit '.$plan->name : 'Create a plan' }}</h1>
            <p class="plan-editor__desc">Pick which product areas schools can use. Unchecked modules are hidden and blocked in their admin panel.</p>
        </div>
        <button type="submit" form="planForm" class="btn btn-primary plan-editor__save">
            <iconify-icon icon="solar:diskette-linear"></iconify-icon>
            {{ $plan->exists ? 'Save plan' : 'Create plan' }}
        </button>
    </div>

    @if($errors->any())
    <div class="plan-editor__errors">
        <strong>Please fix the following:</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form id="planForm" method="POST" action="{{ $plan->exists ? route('platform.plans.update', $plan) : route('platform.plans.store') }}">
        @csrf
        @if($plan->exists) @method('PUT') @endif

        <div class="plan-editor__grid">
            <div class="plan-editor__main">
                {{-- Basics --}}
                <section class="plan-panel">
                    <div class="plan-panel__head">
                        <h2>Plan details</h2>
                        <p>Name and slug appear on pricing pages and Stripe.</p>
                    </div>
                    <div class="plan-panel__body plan-panel__body--grid">
                        <div class="plan-field">
                            <label>Plan name *</label>
                            <input type="text" name="name" required value="{{ old('name', $plan->name) }}" placeholder="Starter">
                        </div>
                        <div class="plan-field">
                            <label>Slug *</label>
                            <input type="text" name="slug" required value="{{ old('slug', $plan->slug) }}" placeholder="starter">
                        </div>
                        <div class="plan-field plan-field--full">
                            <label>Tagline</label>
                            <input type="text" name="tagline" value="{{ old('tagline', $plan->tagline) }}" placeholder="Everything you need to get started — free">
                        </div>
                        <div class="plan-field plan-field--full">
                            <label>Internal description</label>
                            <textarea name="description" rows="2" placeholder="Notes for your team — not shown publicly">{{ old('description', $plan->description) }}</textarea>
                        </div>
                    </div>
                </section>

                {{-- Product modules --}}
                <section class="plan-panel">
                    <div class="plan-panel__head plan-panel__head--row">
                        <div>
                            <h2>Product modules</h2>
                            <p>Check each area this plan unlocks. Schools without a module cannot open it in the sidebar or via direct URL.</p>
                        </div>
                        <div class="plan-module-toolbar">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllModules">Select all</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllModules">Clear all</button>
                        </div>
                    </div>
                    <div class="plan-panel__body">
                        @error('modules')
                        <p class="plan-field-error">{{ $message }}</p>
                        @enderror
                        <div class="plan-modules" id="planModules">
                            @foreach($modulesByGroup as $group => $modules)
                            <div class="plan-module-group">
                                <h3 class="plan-module-group__title">{{ $group }}</h3>
                                <div class="plan-module-grid">
                                    @foreach($modules as $key => $definition)
                                    <label class="plan-module-card {{ in_array($key, $selectedModules, true) ? 'is-checked' : '' }}">
                                        <input type="checkbox" name="modules[]" value="{{ $key }}"
                                               @checked(in_array($key, $selectedModules, true))>
                                        <span class="plan-module-card__check" aria-hidden="true">
                                            <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                                        </span>
                                        <span class="plan-module-card__icon" aria-hidden="true">
                                            <iconify-icon icon="{{ $definition['icon'] ?? 'solar:widget-linear' }}"></iconify-icon>
                                        </span>
                                        <span class="plan-module-card__text">
                                            <strong>{{ $definition['label'] }}</strong>
                                            <small>{{ $definition['description'] ?? '' }}</small>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <p class="plan-modules__hint">
                            <iconify-icon icon="solar:shield-check-linear"></iconify-icon>
                            <span><strong id="moduleCount">{{ count($selectedModules) }}</strong> modules selected — pricing page bullets are built automatically from these.</span>
                        </p>
                    </div>
                </section>

                {{-- Extra marketing lines --}}
                <section class="plan-panel">
                    <div class="plan-panel__head">
                        <h2>Extra pricing bullets</h2>
                        <p>Optional lines for signup &amp; marketing (e.g. “Email support”, “Priority onboarding”). Module features above are added automatically.</p>
                    </div>
                    <div class="plan-panel__body">
                        <div class="plan-features" id="planFeatures">
                            @foreach($extraFeatures as $index => $feature)
                            <div class="plan-feature-row" data-index="{{ $index }}">
                                <span class="plan-feature-row__drag" aria-hidden="true"><iconify-icon icon="solar:hamburger-menu-linear"></iconify-icon></span>
                                <input type="text" name="extra_features[]" value="{{ $feature }}" placeholder="e.g. Email support" class="plan-feature-row__input">
                                <button type="button" class="plan-feature-row__remove" title="Remove line" aria-label="Remove line">
                                    <iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <div class="plan-features__toolbar">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addFeatureBtn">
                                <iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add bullet
                            </button>
                        </div>
                    </div>
                </section>

                {{-- Limits --}}
                <section class="plan-panel">
                    <div class="plan-panel__head">
                        <h2>Package limits</h2>
                        <p>Leave blank for unlimited. These enforce caps per school (enforcement hooks can be added module-by-module).</p>
                    </div>
                    <div class="plan-panel__body plan-panel__body--limits">
                        @foreach($limitDefinitions as $key => $definition)
                        <div class="plan-limit-field">
                            <label for="limit_{{ $key }}">{{ $definition['label'] }}</label>
                            <input type="number" min="1" id="limit_{{ $key }}" name="limits[{{ $key }}]"
                                   value="{{ old('limits.'.$key, $plan->limitValue($key)) }}"
                                   placeholder="{{ $definition['placeholder'] ?? 'Unlimited' }}">
                            @if(!empty($definition['help']))
                            <small>{{ $definition['help'] }}</small>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="plan-editor__aside">
                {{-- Pricing --}}
                <section class="plan-panel plan-panel--sticky">
                    <div class="plan-panel__head">
                        <h2>Pricing</h2>
                    </div>
                    <div class="plan-panel__body">
                        <div class="plan-field">
                            <label>Billing cycle *</label>
                            <div class="plan-intervals" role="group" aria-label="Billing cycle">
                                @foreach(\App\Enums\Platform\PlanBillingInterval::cases() as $interval)
                                <label class="plan-interval {{ $selectedInterval === $interval->value ? 'is-active' : '' }}">
                                    <input type="radio" name="billing_interval" value="{{ $interval->value }}"
                                           @checked($selectedInterval === $interval->value) required>
                                    <span>{{ $interval->label() }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="plan-field-row">
                            <div class="plan-field">
                                <label>Price *</label>
                                <input type="number" step="0.01" min="0" name="price" required value="{{ old('price', $plan->price) }}">
                                <small>Set to <strong>0</strong> for a free plan — no Stripe checkout on signup.</small>
                            </div>
                            <div class="plan-field">
                                <label>Currency</label>
                                <select name="currency">
                                    @foreach(['USD', 'GBP', 'EUR'] as $currency)
                                    <option value="{{ $currency }}" @selected(old('currency', $plan->currency) === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="plan-field" id="trialDaysField">
                            <label>Trial days</label>
                            <input type="number" min="0" max="365" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}">
                            <small>Ignored for lifetime plans (set to 0). Free plans activate immediately.</small>
                        </div>
                        <div class="plan-field">
                            <label>Display order</label>
                            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                        </div>

                        <div class="plan-toggles">
                            <label class="plan-toggle">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                                <span class="plan-toggle__ui"></span>
                                <span class="plan-toggle__text"><strong>Active</strong><small>Show on signup &amp; pricing</small></span>
                            </label>
                            <label class="plan-toggle">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured))>
                                <span class="plan-toggle__ui"></span>
                                <span class="plan-toggle__text"><strong>Featured</strong><small>Highlight on pricing page</small></span>
                            </label>
                            <label class="plan-toggle">
                                <input type="hidden" name="is_default" value="0">
                                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $plan->is_default))>
                                <span class="plan-toggle__ui"></span>
                                <span class="plan-toggle__text"><strong>Default plan</strong><small>Pre-selected on signup</small></span>
                            </label>
                        </div>

                        @if($plan->exists && $plan->isSyncedToStripe())
                        <p class="plan-stripe-note">Stripe price: <code>{{ $plan->stripe_price_id }}</code><br>Re-sync after changing price or billing cycle.</p>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('admin/assets/css/platform-plans.css') }}">
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/platform-plan-editor.js') }}"></script>
@endsection
