<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TikTokIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view integrations');
    }

    public function show(): View
    {
        return view('admin.integrations.tiktok.show');
    }
}
