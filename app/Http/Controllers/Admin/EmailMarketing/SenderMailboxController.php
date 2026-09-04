<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\SenderMailbox;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SenderMailboxController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage mailbox settings');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['organization_id'] = OrganizationContext::idOrFail();
        $data['email'] = strtolower($data['email']);
        $data['is_verified'] = $request->boolean('is_verified');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_default'] = $request->boolean('is_default');
        $data['validate_cert'] = $request->boolean('validate_cert');
        $this->validateDefaultState($data);

        DB::transaction(function () use ($data) {
            if ($data['is_default']) {
                SenderMailbox::forCurrentOrganization()->update(['is_default' => false]);
            }
            $mailbox = SenderMailbox::create($data);
            $this->ensureDefaultSender();
            $this->syncDefaultMailbox();
        });

        return back()->with('success', 'Sender mailbox added.');
    }

    public function update(Request $request, SenderMailbox $senderMailbox): RedirectResponse
    {
        abort_unless((int) $senderMailbox->organization_id === OrganizationContext::idOrFail(), 404);
        $data = $this->validated($request, $senderMailbox);
        $data['email'] = strtolower($data['email']);
        $data['is_verified'] = $request->boolean('is_verified');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');
        $data['validate_cert'] = $request->boolean('validate_cert');
        $this->validateDefaultState($data);
        if (! $request->filled('imap_password')) {
            unset($data['imap_password']);
        }

        DB::transaction(function () use ($senderMailbox, $data) {
            if ($data['is_default']) {
                SenderMailbox::forCurrentOrganization()->whereKeyNot($senderMailbox->id)->update(['is_default' => false]);
            }
            $senderMailbox->update($data);
            $this->ensureDefaultSender();
            $this->syncDefaultMailbox();
        });

        return back()->with('success', 'Sender mailbox updated.');
    }

    public function destroy(SenderMailbox $senderMailbox): RedirectResponse
    {
        abort_unless((int) $senderMailbox->organization_id === OrganizationContext::idOrFail(), 404);
        abort_if($senderMailbox->is_default, 422, 'Choose another default sender before deleting this mailbox.');
        $senderMailbox->delete();
        $this->syncDefaultMailbox();

        return back()->with('success', 'Sender mailbox removed.');
    }

    private function validated(Request $request, ?SenderMailbox $mailbox = null): array
    {
        $orgId = OrganizationContext::idOrFail();

        return $request->validate([
            'name' => 'nullable|string|max:150',
            'email' => ['required', 'email', 'max:255', Rule::unique('em_sender_mailboxes')->where('organization_id', $orgId)->ignore($mailbox)],
            'reply_to' => 'nullable|email|max:255',
            'imap_host' => 'nullable|string|max:255',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_encryption' => 'nullable|in:ssl,tls,none',
            'imap_username' => 'nullable|string|max:255',
            'imap_password' => 'nullable|string|max:255',
            'inbox_folder' => 'nullable|string|max:100',
            'validate_cert' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function syncDefaultMailbox(): void
    {
        $default = SenderMailbox::forCurrentOrganization()
            ->where('is_default', true)
            ->first();

        if (! $default) {
            return;
        }

        $settings = \App\Models\EmailMarketing\MailboxSetting::firstOrNew([
            'organization_id' => OrganizationContext::idOrFail(),
        ]);
        $settings->fill([
            'from_name' => $default->name,
            'from_email' => $default->email,
            'reply_to' => $default->reply_to,
            'imap_host' => $default->imap_host,
            'imap_port' => $default->imap_port,
            'imap_encryption' => $default->imap_encryption,
            'imap_username' => $default->imap_username,
            'inbox_folder' => $default->inbox_folder ?: 'INBOX',
            'validate_cert' => $default->validate_cert,
            'is_enabled' => true,
        ]);
        if ($default->imap_password) {
            $settings->imap_password = $default->imap_password;
        }
        $settings->save();
    }

    private function validateDefaultState(array $data): void
    {
        if (($data['is_default'] ?? false) && (! ($data['is_active'] ?? false) || ! ($data['is_verified'] ?? false))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'is_default' => 'The default sender must be active and verified in SendGrid.',
            ]);
        }
    }

    private function ensureDefaultSender(): void
    {
        SenderMailbox::forCurrentOrganization()
            ->where('is_default', true)
            ->where(fn ($query) => $query->where('is_active', false)->orWhere('is_verified', false))
            ->update(['is_default' => false]);

        if (! SenderMailbox::forCurrentOrganization()->available()->where('is_default', true)->exists()) {
            SenderMailbox::forCurrentOrganization()->available()->orderBy('id')->first()?->update(['is_default' => true]);
        }
    }
}
