<?php

namespace App\Policies\Crm;

use App\Models\Admin;
use App\Models\Crm\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view projects');
    }

    public function view(Admin $admin, Project $project): bool
    {
        return $admin->can('view projects') && $project->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create projects');
    }

    public function update(Admin $admin, Project $project): bool
    {
        return $admin->can('update projects') && $project->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Project $project): bool
    {
        return $admin->can('delete projects') && $project->organization_id === $admin->organization_id;
    }
}
