<?php

namespace App\Policies\Crm;

use App\Models\Admin;
use App\Models\Crm\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view quotations');
    }

    public function view(Admin $admin, Quotation $quotation): bool
    {
        return $admin->can('view quotations') && $quotation->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create quotations');
    }

    public function update(Admin $admin, Quotation $quotation): bool
    {
        return $admin->can('update quotations') && $quotation->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Quotation $quotation): bool
    {
        return $admin->can('delete quotations') && $quotation->organization_id === $admin->organization_id;
    }

    public function send(Admin $admin, Quotation $quotation): bool
    {
        return $admin->can('send quotations') && $quotation->organization_id === $admin->organization_id;
    }

    public function convert(Admin $admin, Quotation $quotation): bool
    {
        return $admin->can('convert quotations') && $quotation->organization_id === $admin->organization_id;
    }

    public function download(Admin $admin, Quotation $quotation): bool
    {
        return $admin->can('view quotations') && $quotation->organization_id === $admin->organization_id;
    }
}
