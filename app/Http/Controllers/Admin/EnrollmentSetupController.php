<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentCourse;
use App\Models\TermsAndCondition;
use App\Services\Tenant\EnrollmentCatalogProvisioner;
use App\Support\EnrollmentCatalog;
use App\Support\OrganizationContext;
use Illuminate\Http\Request;

class EnrollmentSetupController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(plan_module('admissions'), 403);

            return $next($request);
        });
    }

    public function index(EnrollmentCatalogProvisioner $provisioner)
    {
        $organizationId = OrganizationContext::idOrFail();
        $organization = auth('admin')->user()->organization;

        $provisioner->ensureDefaults($organization);

        $sections = [];
        foreach (EnrollmentCatalog::groupedTypes() as $groupKey => $group) {
            $sections[$groupKey] = [
                'label' => $group['label'],
                'types' => [],
            ];

            foreach ($group['types'] as $typeKey => $config) {
                if (! EnrollmentCatalog::userCan('view', $typeKey)) {
                    continue;
                }

                $modelClass = EnrollmentCatalog::modelClass($typeKey);
                $sections[$groupKey]['types'][$typeKey] = [
                    'config' => $config,
                    'items' => $modelClass::forCurrentOrganization()->orderBy('id')->get(),
                    'can_create' => EnrollmentCatalog::userCan('create', $typeKey),
                    'can_edit' => EnrollmentCatalog::userCan('edit', $typeKey),
                    'can_delete' => EnrollmentCatalog::userCan('delete', $typeKey),
                ];
            }
        }

        return view('admin.enrollment-setup.index', [
            'sections' => $sections,
            'terms' => TermsAndCondition::forCurrentOrganization()->first(),
            'courseCount' => StudentCourse::forCurrentOrganization()->count(),
            'activeType' => $this->resolveActiveTab($sections, request('tab')),
        ]);
    }

    private function resolveActiveTab(array $sections, ?string $requested): string
    {
        $keys = [];
        foreach ($sections as $group) {
            foreach ($group['types'] as $typeKey => $_) {
                $keys[] = $typeKey;
            }
        }

        if ($requested && in_array($requested, $keys, true)) {
            return $requested;
        }

        return $keys[0] ?? 'packages';
    }

    public function store(Request $request, string $type)
    {
        abort_unless(EnrollmentCatalog::userCan('create', $type), 403);

        $config = EnrollmentCatalog::type($type);
        $modelClass = EnrollmentCatalog::modelClass($type);
        abort_unless($config && $modelClass, 404);

        $validated = EnrollmentCatalog::validatePayload($type, $request->all());
        $field = $config['field'] ?? 'name';

        $payload = [
            'organization_id' => OrganizationContext::idOrFail(),
            $field => $validated[$field],
            'status' => (int) ($validated['status'] ?? 1),
        ];

        if (! empty($config['requires_group'])) {
            $groupId = \App\Models\StudentGroup::forCurrentOrganization()->value('id');
            abort_unless($groupId, 422, 'Create a year group after the default group is ready.');
            $payload['group_id'] = $groupId;
        }

        $modelClass::create($payload);

        return back()->with('success', ($config['label'] ?? 'Item').' added.');
    }

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(EnrollmentCatalog::userCan('edit', $type), 403);

        $config = EnrollmentCatalog::type($type);
        $modelClass = EnrollmentCatalog::modelClass($type);
        abort_unless($config && $modelClass, 404);

        $record = $modelClass::forCurrentOrganization()->findOrFail($id);
        $validated = EnrollmentCatalog::validatePayload($type, $request->all());
        $field = $config['field'] ?? 'name';

        $record->update([
            $field => $validated[$field],
            'status' => (int) ($validated['status'] ?? $record->status ?? 1),
        ]);

        return back()->with('success', ($config['label'] ?? 'Item').' updated.');
    }

    public function destroy(string $type, int $id)
    {
        abort_unless(EnrollmentCatalog::userCan('delete', $type), 403);

        $modelClass = EnrollmentCatalog::modelClass($type);
        abort_unless($modelClass, 404);

        $modelClass::forCurrentOrganization()->findOrFail($id)->delete();

        return back()->with('success', 'Item removed.');
    }
}
