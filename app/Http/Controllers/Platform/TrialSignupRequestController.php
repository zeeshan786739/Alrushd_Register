<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\TrialSignupStatus;
use App\Http\Controllers\Controller;
use App\Models\TrialSignupRequest;
use App\Services\Platform\AccessApprovalService;
use App\Services\Platform\PlatformActivityLogger;
use Illuminate\Http\Request;

class TrialSignupRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TrialSignupRequest::with(['plan', 'handler', 'organization'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', "%{$search}%")
                    ->orWhere('admin_email', 'like', "%{$search}%")
                    ->orWhere('admin_name', 'like', "%{$search}%");
            });
        }

        return view('platform.trial-requests.index', [
            'requests' => $query->paginate(15)->withQueryString(),
            'statuses' => TrialSignupStatus::cases(),
            'counts' => TrialSignupRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(TrialSignupRequest $trialRequest)
    {
        return view('platform.trial-requests.show', [
            'trialRequest' => $trialRequest->load(['plan', 'handler', 'organization']),
        ]);
    }

    public function update(Request $request, TrialSignupRequest $trialRequest)
    {
        $data = $request->validate([
            'internal_notes' => ['nullable', 'string'],
        ]);

        $trialRequest->update([
            'internal_notes' => $data['internal_notes'] ?? $trialRequest->internal_notes,
            'handled_by' => auth('admin')->id(),
        ]);

        PlatformActivityLogger::log('trial_request.updated', "Trial request notes updated for {$trialRequest->admin_email}");

        return back()->with('success', 'Notes saved.');
    }

    public function approve(TrialSignupRequest $trialRequest, AccessApprovalService $approvals)
    {
        try {
            $organization = $approvals->approveTrial($trialRequest);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('platform.schools.show', $organization)
            ->with('success', 'Free trial approved. A set-password link was emailed to '.$trialRequest->admin_email.'.');
    }

    public function reject(Request $request, TrialSignupRequest $trialRequest, AccessApprovalService $approvals)
    {
        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $approvals->rejectTrial($trialRequest, $data['rejection_reason'] ?? null);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('platform.trial-requests.index')
            ->with('success', 'Trial request rejected and the applicant was notified.');
    }

    public function destroy(TrialSignupRequest $trialRequest)
    {
        $trialRequest->delete();

        return redirect()->route('platform.trial-requests.index')->with('success', 'Trial request deleted.');
    }
}
