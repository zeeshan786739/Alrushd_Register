<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdmissionDate;
use App\Models\Country;
use App\Models\FormSubmission;
use App\Models\Gender;
use App\Models\Group;
use App\Models\Nationality;
use App\Models\PaymentCountry;
use App\Models\RelationShip;
use App\Models\School;
use App\Models\StudentGroup;
use App\Models\StudentPackage;
use App\Models\StudentYear;
use App\Models\TermsAndCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FrontendFormDataController extends Controller
{
    public function csrf(): JsonResponse
    {
        return response()->json(['csrf_token' => csrf_token()]);
    }

    public function options(): JsonResponse
    {
        $countries = Country::query()->orderBy('name')->get(['id', 'name']);
        $nationalities = Nationality::where('status', 1)->orderBy('name')->get(['id', 'name']);
        if ($nationalities->isEmpty()) {
            $nationalities = $countries;
        }

        $payload = [
            'countries' => $countries,
            'all_countries' => $countries,
            'nationalities' => $nationalities,
        ];

        $optional = [
            'payment_countries' => fn () => PaymentCountry::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'genders' => fn () => Gender::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'relationships' => fn () => RelationShip::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'admission_dates' => fn () => AdmissionDate::where('status', 1)
                ->orderBy('date')
                ->get(['id', 'date'])
                ->map(fn ($row) => ['id' => $row->id, 'name' => $row->date, 'date' => $row->date])
                ->values(),
            'schools' => fn () => School::where('status', 1)->orderBy('name')->get(['id', 'name', 'image', 'timezone', 'description']),
            'student_groups' => fn () => StudentGroup::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'student_years' => fn () => StudentYear::where('status', 1)->orderBy('name')->get(['id', 'name', 'group_id']),
            'student_packages' => fn () => StudentPackage::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'debit_groups' => fn () => Group::where('status', 1)
                ->orderBy('serial')
                ->get(['id', 'title'])
                ->map(fn ($row) => ['id' => $row->id, 'name' => $row->title])
                ->values(),
        ];

        foreach ($optional as $key => $resolver) {
            try {
                $payload[$key] = $resolver();
            } catch (\Throwable) {
                $payload[$key] = [];
            }
        }

        try {
            $payload['terms'] = TermsAndCondition::first();
        } catch (\Throwable) {
            $payload['terms'] = null;
        }

        foreach (config('form_options', []) as $key => $values) {
            if (in_array($key, ['field_types'], true) || ! is_array($values)) {
                continue;
            }
            $payload[$key] = array_map(fn ($item) => is_array($item) ? $item : ['value' => $item, 'label' => $item], $values);
        }

        return response()->json($payload);
    }

    public function admission(Request $request, ?int $id = null): JsonResponse
    {
        $submissionId = $id ?? $request->header('X-Submission-Id');

        if (!$submissionId) {
            return response()->json(['data' => null]);
        }

        $submission = FormSubmission::with(['students.year', 'students.package'])->find($submissionId);

        if (!$submission) {
            return response()->json(['data' => null], 404);
        }

        return response()->json([
            'data' => $submission,
            'submitted_packages' => $submission->packages ? json_decode($submission->packages, true) : [],
        ]);
    }
}
