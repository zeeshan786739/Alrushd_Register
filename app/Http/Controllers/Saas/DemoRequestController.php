<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\Request;

class DemoRequestController extends Controller
{
    public function create()
    {
        return view('saas.book-demo');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'max:128'],
            'students_count' => ['nullable', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        DemoRequest::create($data + ['source' => 'landing']);

        return redirect()->route('saas.demo.create')
            ->with('demo_submitted', true);
    }
}
