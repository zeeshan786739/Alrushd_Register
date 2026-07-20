<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class CrmOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::default();

        Admin::query()->whereNull('organization_id')->update([
            'organization_id' => $organization->id,
        ]);

        \App\Models\Form::query()->whereNull('organization_id')->update([
            'organization_id' => $organization->id,
        ]);

        \App\Models\FormEntry::query()->whereNull('organization_id')->update([
            'organization_id' => $organization->id,
        ]);
    }
}
