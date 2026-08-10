<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeBillingService $billing)
    {
        if (! $billing->isConfigured()) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $payload = $request->getContent();
        $webhookSecret = $billing->webhookSecret();

        try {
            if ($webhookSecret) {
                $event = Webhook::constructEvent(
                    $payload,
                    $request->header('Stripe-Signature', ''),
                    $webhookSecret
                );
            } else {
                $event = \Stripe\Event::constructFrom(json_decode($payload, true) ?: []);
            }
        } catch (\Throwable $e) {
            Log::warning('Platform Stripe webhook rejected: ' . $e->getMessage());

            return response()->json(['error' => 'invalid payload'], 400);
        }

        try {
            $billing->handleEvent($event);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'processing failed'], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
