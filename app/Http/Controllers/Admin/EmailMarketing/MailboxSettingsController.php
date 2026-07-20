<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\MailboxSetting;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailboxSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage mailbox settings');
    }

    public function edit(): View
    {
        $settings = MailboxSetting::firstOrNew([
            'organization_id' => OrganizationContext::idOrFail(),
        ]);

        return view('admin.email-marketing.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_name' => 'nullable|string|max:150',
            'from_email' => 'nullable|email',
            'reply_to' => 'nullable|email',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:tls,ssl,',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'imap_host' => 'nullable|string|max:255',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_encryption' => 'nullable|in:tls,ssl,',
            'imap_username' => 'nullable|string|max:255',
            'imap_password' => 'nullable|string|max:255',
            'inbox_folder' => 'nullable|string|max:100',
            'sent_folder' => 'nullable|string|max:100',
            'validate_cert' => 'nullable|boolean',
            'tracking_enabled' => 'nullable|boolean',
            'is_enabled' => 'nullable|boolean',
        ]);

        $settings = MailboxSetting::firstOrNew([
            'organization_id' => OrganizationContext::idOrFail(),
        ]);

        // Preserve secrets when blank.
        if ($request->filled('smtp_password')) {
            $settings->smtp_password = $validated['smtp_password'];
        }
        if ($request->filled('imap_password')) {
            $settings->imap_password = $validated['imap_password'];
        }
        unset($validated['smtp_password'], $validated['imap_password']);

        $settings->fill($validated);
        $settings->validate_cert = $request->boolean('validate_cert', true);
        $settings->tracking_enabled = $request->boolean('tracking_enabled', true);
        $settings->is_enabled = $request->boolean('is_enabled');
        $settings->organization_id = OrganizationContext::idOrFail();
        $settings->save();

        return back()->with('success', 'Mailbox settings saved.');
    }
}
