<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
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
        ]);
    }

    public function create()
    {
        return view('platform.plans.form', ['plan' => new SaasPlan(['currency' => 'USD', 'billing_interval' => 'month', 'trial_days' => 14, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $plan = SaasPlan::create($this->validated($request));

        PlatformActivityLogger::log('plan.created', "Plan \"{$plan->name}\" created ({$plan->formattedPrice()}/{$plan->billing_interval})");

        return redirect()->route('platform.plans.index')->with('success', 'Plan created.');
    }

    public function edit(SaasPlan $plan)
    {
        return view('platform.plans.form', ['plan' => $plan]);
    }

    public function update(Request $request, SaasPlan $plan)
    {
        $plan->update($this->validated($request, $plan));

        PlatformActivityLogger::log('plan.updated', "Plan \"{$plan->name}\" updated");

        return redirect()->route('platform.plans.index')->with('success', 'Plan updated. Re-sync to Stripe if the price changed.');
    }

    public function toggle(SaasPlan $plan)
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('success', $plan->is_active ? 'Plan activated.' : 'Plan deactivated.');
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

            return back()->with('error', 'Stripe sync failed: ' . $e->getMessage());
        }

        PlatformActivityLogger::log('plan.stripe_synced', "Plan \"{$plan->name}\" synced to Stripe ({$plan->stripe_price_id})");

        return back()->with('success', 'Plan synced to Stripe.');
    }

    public function destroy(SaasPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'This plan has subscriptions and cannot be deleted. Deactivate it instead.');
        }

        $plan->delete();

        PlatformActivityLogger::log('plan.deleted', "Plan \"{$plan->name}\" deleted");

        return back()->with('success', 'Plan deleted.');
    }

    private function validated(Request $request, ?SaasPlan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('saas_plans', 'slug')->ignore($plan?->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_interval' => ['required', Rule::in(['month', 'year'])],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'features_text' => ['nullable', 'string'],
            'max_admins' => ['nullable', 'integer', 'min:1'],
            'max_leads' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $features = collect(preg_split('/\r\n|\r|\n/', (string) ($data['features_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return [
            'name' => $data['name'],
            'slug' => Str::slug($data['slug']),
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'billing_interval' => $data['billing_interval'],
            'trial_days' => $data['trial_days'],
            'features' => $features,
            'limits' => array_filter([
                'max_admins' => $data['max_admins'] ?? null,
                'max_leads' => $data['max_leads'] ?? null,
            ]),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
