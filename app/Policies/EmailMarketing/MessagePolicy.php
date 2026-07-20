<?php

namespace App\Policies\EmailMarketing;

use App\Models\Admin;
use App\Models\EmailMarketing\Message;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view inbox') || $admin->can('view sent emails') || $admin->can('manage drafts');
    }

    public function view(Admin $admin, Message $message): bool
    {
        return $admin->can('read email messages')
            && $message->organization_id === $admin->organization_id;
    }

    public function compose(Admin $admin): bool
    {
        return $admin->can('compose emails');
    }

    public function send(Admin $admin): bool
    {
        return $admin->can('send emails');
    }

    public function update(Admin $admin, Message $message): bool
    {
        return ($admin->can('manage drafts') || $admin->can('star emails'))
            && $message->organization_id === $admin->organization_id;
    }

    public function delete(Admin $admin, Message $message): bool
    {
        return ($admin->can('manage drafts') || $admin->can('read email messages'))
            && $message->organization_id === $admin->organization_id;
    }

    public function sync(Admin $admin): bool
    {
        return $admin->can('sync inbox');
    }

    public function download(Admin $admin, Message $message): bool
    {
        return $admin->can('download email attachments')
            && $message->organization_id === $admin->organization_id;
    }
}
