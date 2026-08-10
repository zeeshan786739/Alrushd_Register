<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\DemoRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Services\Platform\PlatformActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemoRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DemoRequest::with(['handler', 'convertedOrganization'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('organization_name', 'like', "%{$search}%");
            });
        }

        return view('platform.demo-requests.index', [
            'demoRequests' => $query->paginate(15)->withQueryString(),
            'statuses' => DemoRequestStatus::cases(),
            'counts' => DemoRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(DemoRequest $demoRequest)
    {
        return view('platform.demo-requests.show', [
            'demoRequest' => $demoRequest->load(['handler', 'convertedOrganization']),
            'statuses' => DemoRequestStatus::cases(),
        ]);
    }

    public function update(Request $request, DemoRequest $demoRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(DemoRequestStatus::class)],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $demoRequest->update([
            'status' => $data['status'],
            'internal_notes' => $data['internal_notes'] ?? $demoRequest->internal_notes,
            'handled_by' => auth('admin')->id(),
        ]);

        PlatformActivityLogger::log('demo_request.updated', "Demo request from {$demoRequest->email} → " . DemoRequestStatus::from($data['status'])->label());

        return back()->with('success', 'Demo request updated.');
    }

    public function destroy(DemoRequest $demoRequest)
    {
        $demoRequest->delete();

        return redirect()->route('platform.demo-requests.index')->with('success', 'Demo request deleted.');
    }
}
