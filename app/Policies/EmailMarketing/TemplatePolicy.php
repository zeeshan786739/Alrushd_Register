<?php

namespace App\Policies\EmailMarketing;

use App\Models\Admin;
use App\Models\EmailMarketing\Template;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view templates');
    }

    public function view(Admin $admin, Template $template): bool
    {
        return $admin->can('view templates')
            && $template->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create templates');
    }

    public function update(Admin $admin, Template $template): bool
    {
        return $admin->can('update templates')
            && $template->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Template $template): bool
    {
        return $admin->can('delete templates')
            && $template->organization_id === $admin->organization_id;
    }
}
