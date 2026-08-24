<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\PlanBillingInterval;
use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
use App\Support\PlanEntitlements;
use App\Support\SaasPlanDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index(StripeBillingService $billing)
    {
        return view('platform.plans.index', [
            'plans' => SaasPlan::withCount('subscriptions')->ordered()->get(),
            'stripeConfigured' => $billing->isConfigured(),
            'defaultPlan' => SaasPlan::defaultPlan(),
        ]);
    }

    public function create()
    {
        $defaults = SaasPlanDefaults::forNewPlan();

        return view('platform.plans.form', [
            'plan' => new SaasPlan([
                'currency' => $defaults['currency'] ?? 'USD',
                'billing_interval' => $defaults['billing_interval'] ?? 'month',
                'trial_days' => $defaults['trial_days'] ?? 14,
                'price' => $defaults['price'] ?? 0,
                'is_active' => $defaults['is_active'] ?? true,
                'is_featured' => $defaults['is_featured'] ?? false,
                'is_default' => $defaults['is_default'] ?? false,
                'sort_order' => $defaults['sort_order'] ?? 0,
                'modules' => PlanEntitlements::allModuleKeys(),
            ]),
            'limitDefinitions' => SaasPlanDefaults::limitDefinitions(),
            'moduleCatalog' => PlanEntitlements::moduleCatalog(),
        ]);
    }

    public function store(Request $request)
    {
        $plan = SaasPlan::create($this->validated($request));
        SaasPlanDefaults::ensureDefaultPlanExists();

        PlatformActivityLogger::log('plan.created', "Plan \"{$plan->name}\" created ({$plan->formattedPriceWithInterval()})");

        return redirect()->route('platform.plans.index')->with('success', 'Plan created.');
    }

    public function edit(SaasPlan $plan)
    {
        return view('platform.plans.form', [
            'plan' => $plan,
            'limitDefinitions' => SaasPlanDefaults::limitDefinitions(),
            'moduleCatalog' => PlanEntitlements::moduleCatalog(),
        ]);
    }

    public function update(Request $request, SaasPlan $plan)
    {
        $plan->update($this->validated($request, $plan));
        SaasPlanDefaults::ensureDefaultPlanExists();

        PlatformActivityLogger::log('plan.updated', "Plan \"{$plan->name}\" updated");

        return redirect()->route('platform.plans.index')->with('success', 'Plan saved. Re-sync to Stripe if pricing changed.');
    }

    public function toggle(SaasPlan $plan)
    {
        if ($plan->is_active && $plan->is_default) {
            return back()->with('error', 'Cannot hide the default signup plan. Set another plan as default first.');
        }

        $plan->update(['is_active' => ! $plan->is_active]);
        SaasPlanDefaults::ensureDefaultPlanExists();

        return back()->with('success', $plan->is_active ? 'Plan activated.' : 'Plan deactivated.');
    }

    public function setDefault(SaasPlan $plan)
    {
        if (! $plan->is_active) {
            return back()->with('error', 'Activate the plan before setting it as the default signup plan.');
        }

        $plan->update(['is_default' => true]);

        PlatformActivityLogger::log('plan.default_set', "Plan \"{$plan->name}\" set as default signup plan");

        return back()->with('success', "{$plan->name} is now the default plan on signup.");
    }

    public function syncStripe(SaasPlan $plan, StripeBillingService $billing)
    {
        if (! $billing->isConfigured()) {
            return back()->with('error', 'Stripe is not configured. Add your platform Stripe keys in Settings first.');
        }

        try {
            $billing->syncPlan($plan);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Stripe sync failed: '.$e->getMessage());
        }

        PlatformActivityLogger::log('plan.stripe_synced', "Plan \"{$plan->name}\" synced to Stripe ({$plan->stripe_price_id})");

        return back()->with('success', 'Plan synced to Stripe.');
    }

    public function destroy(SaasPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'This plan has subscriptions and cannot be deleted. Deactivate it instead.');
        }

        if ($plan->is_default) {
            return back()->with('error', 'Cannot delete the default plan. Set another default first.');
        }

        $plan->delete();
        SaasPlanDefaults::ensureDefaultPlanExists();

        PlatformActivityLogger::log('plan.deleted', "Plan \"{$plan->name}\" deleted");

        return back()->with('success', 'Plan deleted.');
    }

    private function validated(Request $request, ?SaasPlan $plan = null): array
    {
        $limitKeys = array_keys(SaasPlanDefaults::limitDefinitions());

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('saas_plans', 'slug')->ignore($plan?->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_interval' => ['required', Rule::enum(PlanBillingInterval::class)],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['string', Rule::in(PlanEntitlements::allModuleKeys())],
            'extra_features' => ['nullable', 'array'],
            'extra_features.*' => ['nullable', 'string', 'max:500'],
            'limits' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        foreach ($limitKeys as $key) {
            $rules["limits.{$key}"] = ['nullable', 'integer', 'min:1'];
        }

        $data = $request->validate($rules);

        $modules = array_values(array_unique($data['modules'] ?? []));

        $extraFeatures = collect($data['extra_features'] ?? [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        $limits = [];
        foreach ($limitKeys as $key) {
            $value = $data['limits'][$key] ?? null;
            if ($value !== null && $value !== '') {
                $limits[$key] = (int) $value;
            }
        }

        $interval = PlanBillingInterval::from($data['billing_interval']);

        return [
            'name' => $data['name'],
            'slug' => Str::slug($data['slug']),
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'billing_interval' => $interval->value,
            'trial_days' => $interval === PlanBillingInterval::Lifetime ? 0 : $data['trial_days'],
            'modules' => $modules,
            'features' => SaasPlan::marketingFeaturesFromModules($modules, $extraFeatures),
            'limits' => $limits,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'is_default' => $request->boolean('is_default'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
