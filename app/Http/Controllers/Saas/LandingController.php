<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SaasPlan;

class LandingController extends Controller
{
    public function index()
    {
        return view('saas.landing', [
            'plans' => SaasPlan::active()->ordered()->get(),
            'schoolsCount' => max(Organization::whereIn('status', ['active', 'trial'])->count(), 1),
        ]);
    }
}
