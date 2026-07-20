<?php

namespace App\Policies\Crm;

use App\Models\Admin;
use App\Models\Crm\Lead;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view leads');
    }

    public function view(Admin $admin, Lead $lead): bool
    {
        return $admin->can('view leads') && $lead->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create leads');
    }

    public function update(Admin $admin, Lead $lead): bool
    {
        return $admin->can('update leads') && $lead->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Lead $lead): bool
    {
        return $admin->can('delete leads') && $lead->organization_id === $admin->organization_id;
    }

    public function assign(Admin $admin, Lead $lead): bool
    {
        return $admin->can('assign leads') && $lead->organization_id === $admin->organization_id;
    }

    public function convert(Admin $admin, Lead $lead): bool
    {
        return $admin->can('convert leads') && $lead->organization_id === $admin->organization_id;
    }

    public function export(Admin $admin, mixed $model = null): bool
    {
        return $admin->can('export leads');
    }
}
