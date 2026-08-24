<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\OrganizationPaymentSetting;
use App\Services\Tenant\TenantStripeService;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class PaymentSettingsController extends Controller
{
    public function edit(): View
    {
        $organizationId = OrganizationContext::idOrFail();
        $settings = OrganizationPaymentSetting::query()->firstOrNew(['organization_id' => $organizationId]);
        $stripe = TenantStripeService::forOrganization($organizationId);

        return view('admin.account.payments.edit', [
            'settings' => $settings,
            'stripe' => $stripe,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organizationId = OrganizationContext::idOrFail();

        $data = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'test_mode' => ['nullable', 'boolean'],
            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'statement_descriptor' => ['nullable', 'string', 'max:22'],
        ]);

        $settings = OrganizationPaymentSetting::query()->firstOrNew(['organization_id' => $organizationId]);
        $settings->is_enabled = $request->boolean('is_enabled');
        $settings->test_mode = $request->boolean('test_mode');
        $settings->stripe_publishable_key = $data['stripe_publishable_key'] ?? null;
        $settings->statement_descriptor = $data['statement_descriptor'] ?? null;

        if ($request->filled('stripe_secret')) {
            $settings->stripe_secret = $request->input('stripe_secret');
        }

        if ($request->filled('stripe_webhook_secret')) {
            $settings->stripe_webhook_secret = $request->input('stripe_webhook_secret');
        }

        $settings->updated_by = auth('admin')->id();
        $settings->save();

        return redirect()
            ->route('admin.account.payments.edit')
            ->with('success', 'Payment settings saved.');
    }

    public function test(Request $request): RedirectResponse
    {
        $organizationId = OrganizationContext::idOrFail();
        $settings = OrganizationPaymentSetting::query()->where('organization_id', $organizationId)->first();

        if (! $settings?->stripe_secret) {
            return back()->with('error', 'Save a secret key before testing the connection.');
        }

        try {
            $client = new StripeClient($settings->stripe_secret);
            $balance = $client->balance->retrieve();

            $settings->last_verified_at = now();
            $settings->save();

            $currency = strtoupper($balance->available[0]->currency ?? 'usd');

            return back()->with('success', 'Stripe connection verified. Account balance available in '.$currency.'.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Could not connect to Stripe: '.$e->getMessage());
        }
    }
}
