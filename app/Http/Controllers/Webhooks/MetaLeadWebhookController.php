<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Integrations\Meta\MetaWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MetaLeadWebhookController extends Controller
{
    public function verify(Request $request): Response|SymfonyResponse
    {
        $mode = (string) $request->query('hub_mode');
        $token = (string) $request->query('hub_verify_token');
        $challenge = (string) $request->query('hub_challenge');

        if ($mode === 'subscribe' && hash_equals((string) config('integrations.meta.webhook_verify_token'), $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        abort(SymfonyResponse::HTTP_FORBIDDEN);
    }

    public function handle(Request $request, MetaWebhookHandler $handler): Response
    {
        try {
            $handler->handle($request->all());
        } catch (\Throwable $exception) {
            Log::error('Meta lead webhook handler failed', [
                'message' => $exception->getMessage(),
                'payload' => $request->all(),
            ]);

            report($exception);
        }

        return response('EVENT_RECEIVED', 200);
    }
}
