<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Integrations\TikTok\TikTokWebhookAuthenticator;
use App\Services\Integrations\TikTok\TikTokWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TikTokLeadWebhookController extends Controller
{
    public function handle(
        Request $request,
        TikTokWebhookAuthenticator $authenticator,
        TikTokWebhookHandler $handler,
    ): Response {
        if (! $authenticator->isValid($request)) {
            return response('Invalid signature', 403);
        }

        $payload = json_decode($request->getContent(), true);
        if (! is_array($payload)) {
            return response('OK', 200);
        }

        $handler->handle($payload);

        return response('OK', 200);
    }
}
