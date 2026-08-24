<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminDashboard;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $admin = auth('admin')->user();
        abort_unless($admin, 403);

        return view('admin.dashboard', AdminDashboard::data($admin));
    }
}
