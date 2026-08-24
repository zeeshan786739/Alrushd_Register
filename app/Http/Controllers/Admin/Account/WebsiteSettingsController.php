<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Support\OrganizationUrls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebsiteSettingsController extends Controller
{
    public function edit(): View
    {
        $organization = auth('admin')->user()->organization;
        abort_unless($organization, 404);

        if (! $organization->custom_domain_verification_token) {
            $organization->update([
                'custom_domain_verification_token' => Str::random(32),
            ]);
            $organization->refresh();
        }

        return view('admin.account.website.edit', [
            'organization' => $organization,
            'publicUrl' => $organization->publicWebsiteUrl(),
            'verificationHost' => OrganizationUrls::verificationHost(),
            'verificationRecord' => OrganizationUrls::verificationRecord($organization),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = auth('admin')->user()->organization;
        abort_unless($organization, 404);

        $data = $request->validate([
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
                'unique:organizations,custom_domain,'.$organization->id,
            ],
        ]);

        $domain = filled($data['custom_domain'])
            ? Str::lower(trim($data['custom_domain']))
            : null;

        if ($domain !== $organization->custom_domain) {
            $organization->update([
                'custom_domain' => $domain,
                'custom_domain_verified_at' => null,
            ]);
        }

        return back()->with('success', $domain
            ? 'Custom domain saved. Add the DNS record below, then click Verify.'
            : 'Custom domain removed. Your site uses the default Enrolliq link.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $organization = auth('admin')->user()->organization;
        abort_unless($organization, 404);

        abort_unless($organization->custom_domain, 422, 'Save a custom domain first.');

        $host = OrganizationUrls::verificationHost();
        $expected = OrganizationUrls::verificationRecord($organization);
        $records = @dns_get_record($host, DNS_TXT) ?: [];

        $verified = collect($records)->contains(fn ($record) => str_contains((string) ($record['txt'] ?? ''), $expected));

        if (! $verified) {
            return back()->with('error', 'DNS verification not detected yet. TXT record may take up to 24 hours to propagate.');
        }

        $organization->update(['custom_domain_verified_at' => now()]);

        return back()->with('success', 'Custom domain verified. Your website is live at https://'.$organization->custom_domain);
    }
}
