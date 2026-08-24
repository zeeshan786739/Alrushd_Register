<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Support\AccountHubHelper;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $admin = auth('admin')->user();
        abort_unless($admin?->organization, 404);

        return view('admin.account.index', [
            'stats' => AccountHubHelper::stats($admin->organization),
            'organization' => $admin->organization,
            'admin' => $admin,
        ]);
    }
}
