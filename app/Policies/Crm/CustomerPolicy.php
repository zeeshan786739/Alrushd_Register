<?php

namespace App\Policies\Crm;

use App\Models\Admin;
use App\Models\Crm\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view customers');
    }

    public function view(Admin $admin, Customer $customer): bool
    {
        return $admin->can('view customers') && $customer->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create customers');
    }

    public function update(Admin $admin, Customer $customer): bool
    {
        return $admin->can('update customers') && $customer->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Customer $customer): bool
    {
        return $admin->can('delete customers') && $customer->organization_id === $admin->organization_id;
    }

    public function export(Admin $admin, mixed $model = null): bool
    {
        return $admin->can('export customers');
    }
}
