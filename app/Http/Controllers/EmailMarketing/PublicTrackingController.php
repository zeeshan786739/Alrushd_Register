<?php

namespace App\Http\Controllers\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Services\EmailMarketing\TrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicTrackingController extends Controller
{
    public function __construct(private TrackingService $tracking)
    {
    }

    public function open(string $token): Response
    {
        $this->tracking->recordOpen($token);

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function click(Request $request, string $token): RedirectResponse
    {
        $url = (string) $request->query('url', '/');
        $this->tracking->recordClick($token, $url);

        return redirect()->away($url);
    }

    public function unsubscribeShow(string $token): View
    {
        return view('admin.email-marketing.unsubscribe', compact('token'));
    }

    public function unsubscribeStore(string $token): View
    {
        $suppression = $this->tracking->unsubscribeFromRecipientToken($token);

        return view('admin.email-marketing.unsubscribe-done', [
            'success' => (bool) $suppression,
        ]);
    }
}
