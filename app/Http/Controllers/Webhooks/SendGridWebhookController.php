<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\EmailMarketing\SendGridEventService;
use App\Services\EmailMarketing\SendGridInboundService;
use App\Services\EmailMarketing\SendGridWebhookVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SendGridWebhookController extends Controller
{
    public function events(
        Request $request,
        SendGridWebhookVerifier $verifier,
        SendGridEventService $events,
    ): JsonResponse {
        if (! $verifier->verifyEventWebhook($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->json()->all();
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload'], 422);
        }

        // SendGrid sends a JSON array of events.
        $list = array_is_list($payload) ? $payload : [$payload];
        $result = $events->ingest($list);

        return response()->json(['ok' => true] + $result);
    }

    public function inbound(
        Request $request,
        SendGridWebhookVerifier $verifier,
        SendGridInboundService $inbound,
    ): Response|JsonResponse {
        if (! $verifier->verifyInboundBasicAuth($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = $inbound->ingest($request);
        if (! $message) {
            // Acknowledge to avoid endless retries for unresolvable orgs.
            return response()->json(['ok' => true, 'stored' => false], 202);
        }

        return response()->json(['ok' => true, 'stored' => true, 'id' => $message->id], 201);
    }
}
