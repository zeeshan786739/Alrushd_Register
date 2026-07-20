<?php

namespace App\Policies\Crm;

use App\Models\Admin;
use App\Models\FormEntry;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view form submissions');
    }

    public function view(Admin $admin, FormEntry $formEntry): bool
    {
        return $admin->can('view form submissions') && $this->sameOrganization($admin, $formEntry);
    }

    public function update(Admin $admin, FormEntry $formEntry): bool
    {
        return $admin->can('update form submissions') && $this->sameOrganization($admin, $formEntry);
    }

    public function delete(Admin $admin, FormEntry $formEntry): bool
    {
        return $admin->can('delete form submissions') && $this->sameOrganization($admin, $formEntry);
    }

    public function export(Admin $admin, mixed $model = null): bool
    {
        return $admin->can('export form submissions');
    }

    public function convert(Admin $admin, FormEntry $formEntry): bool
    {
        return $admin->can('convert form submissions') && $this->sameOrganization($admin, $formEntry);
    }

    private function sameOrganization(Admin $admin, FormEntry $formEntry): bool
    {
        if (! $admin->organization_id) {
            return false;
        }

        if ($formEntry->organization_id) {
            return (int) $formEntry->organization_id === (int) $admin->organization_id;
        }

        return $formEntry->form?->organization_id === $admin->organization_id;
    }
}
