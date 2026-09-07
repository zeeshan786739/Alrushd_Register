<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\DemoRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\SaasPlan;
use App\Services\Platform\AccessApprovalService;
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
            'plans' => SaasPlan::active()->ordered()->get(),
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

    public function approve(Request $request, DemoRequest $demoRequest, AccessApprovalService $approvals)
    {
        if (! $demoRequest->canGrantAccess()) {
            return back()->with('error', 'Demo access has already been granted or this request is closed.');
        }

        $data = $request->validate([
            'saas_plan_id' => ['nullable', 'exists:saas_plans,id'],
        ]);

        $plan = ! empty($data['saas_plan_id'])
            ? SaasPlan::find($data['saas_plan_id'])
            : null;

        try {
            $result = $approvals->approveDemo($demoRequest, $plan);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('platform.schools.show', $result['organization'])
            ->with('success', 'Demo access approved. A set-password link was emailed to '.$demoRequest->email.'.');
    }

    public function destroy(DemoRequest $demoRequest)
    {
        $demoRequest->delete();

        return redirect()->route('platform.demo-requests.index')->with('success', 'Demo request deleted.');
    }
}
