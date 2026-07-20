<?php

namespace App\Policies\EmailMarketing;

use App\Models\Admin;
use App\Models\EmailMarketing\Campaign;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view campaigns');
    }

    public function view(Admin $admin, Campaign $campaign): bool
    {
        return $admin->can('view campaigns')
            && $campaign->organization_id === $admin->organization_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('create campaigns');
    }

    public function update(Admin $admin, Campaign $campaign): bool
    {
        return $admin->can('update campaigns')
            && $campaign->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Campaign $campaign): bool
    {
        return $admin->can('delete campaigns')
            && $campaign->organization_id === $admin->organization_id;
    }

    public function send(Admin $admin, Campaign $campaign): bool
    {
        return $admin->can('send campaigns')
            && $campaign->organization_id === $admin->organization_id;
    }
}
