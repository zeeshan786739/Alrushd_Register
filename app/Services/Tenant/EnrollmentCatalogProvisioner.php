<?php

namespace App\Services\Tenant;

use App\Models\AdmissionDate;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\Organization;
use App\Models\PaymentCountry;
use App\Models\RelationShip;
use App\Models\School;
use App\Models\StudentGroup;
use App\Models\StudentLanguage;
use App\Models\StudentPackage;
use App\Models\StudentSubject;
use App\Models\StudentYear;
use App\Models\TermsAndCondition;
use App\Support\EnrollmentCatalog;

class EnrollmentCatalogProvisioner
{
    public function ensureDefaults(Organization $organization): void
    {
        $this->ensureTerms($organization);
        $groupId = $this->ensureDefaultGroup($organization);

        foreach (EnrollmentCatalog::types() as $type => $config) {
            if ($type === 'enrollment_dates') {
                $this->seedEnrollmentDates($organization);

                continue;
            }

            if ($type === 'campuses') {
                $this->seedCampuses($organization);

                continue;
            }

            $modelClass = EnrollmentCatalog::modelClass($type);
            if (! $modelClass) {
                continue;
            }

            if ($modelClass::forOrganization($organization->id)->exists()) {
                continue;
            }

            $defaults = EnrollmentCatalog::defaults($type);
            if ($defaults === []) {
                continue;
            }

            foreach ($defaults as $value) {
                $payload = [
                    'organization_id' => $organization->id,
                    'status' => 1,
                    ($config['field'] ?? 'name') => $value,
                ];

                if (! empty($config['requires_group']) && $groupId) {
                    $payload['group_id'] = $groupId;
                }

                $modelClass::create($payload);
            }
        }
    }

    private function seedCampuses(Organization $organization): void
    {
        if (School::forOrganization($organization->id)->exists()) {
            return;
        }

        School::create([
            'organization_id' => $organization->id,
            'name' => $organization->name.' — Main campus',
            'timezone' => $organization->timezone ?? 'UTC',
            'status' => 1,
        ]);
    }

    private function ensureDefaultGroup(Organization $organization): ?int
    {
        $group = StudentGroup::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'General',
            ],
            ['status' => 1]
        );

        return $group->id;
    }

    private function ensureTerms(Organization $organization): void
    {
        TermsAndCondition::firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'terms_description' => 'Please read and accept our enrollment terms before submitting your application.',
                'form_description' => 'By submitting this form you agree to our terms and conditions.',
            ]
        );
    }

    private function seedEnrollmentDates(Organization $organization): void
    {
        if (AdmissionDate::forOrganization($organization->id)->exists()) {
            return;
        }

        $defaults = EnrollmentCatalog::defaults('enrollment_dates');
        if ($defaults === []) {
            AdmissionDate::create([
                'organization_id' => $organization->id,
                'date' => now()->addMonth()->startOfMonth()->toDateString(),
                'status' => 1,
            ]);

            return;
        }

        foreach ($defaults as $date) {
            AdmissionDate::create([
                'organization_id' => $organization->id,
                'date' => $date,
                'status' => 1,
            ]);
        }
    }
}
