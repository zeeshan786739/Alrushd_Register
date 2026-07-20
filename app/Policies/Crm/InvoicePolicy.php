<?php

namespace App\Policies\Crm;

use App\Models\Admin;
use App\Models\Crm\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view invoices');
    }

    public function view(Admin $admin, Invoice $invoice): bool
    {
        return $admin->can('view invoices') && $invoice->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create invoices');
    }

    public function update(Admin $admin, Invoice $invoice): bool
    {
        return $admin->can('update invoices') && $invoice->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Invoice $invoice): bool
    {
        return $admin->can('delete invoices') && $invoice->organization_id === $admin->organization_id;
    }

    public function send(Admin $admin, Invoice $invoice): bool
    {
        return $admin->can('send invoices') && $invoice->organization_id === $admin->organization_id;
    }

    public function recordPayment(Admin $admin, Invoice $invoice): bool
    {
        return $admin->can('record invoice payments') && $invoice->organization_id === $admin->organization_id;
    }

    public function download(Admin $admin, Invoice $invoice): bool
    {
        return $admin->can('view invoices') && $invoice->organization_id === $admin->organization_id;
    }
}
