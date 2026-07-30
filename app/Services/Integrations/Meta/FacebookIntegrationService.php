<?php

namespace App\Services\Integrations\Meta;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Models\Admin;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\IntegrationFormMapping;
use App\Models\Organization;
use App\Support\OrganizationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacebookIntegrationService
{
    public function __construct(
        private MetaGraphClient $graphClient,
    ) {}

    public function connectionForCurrentOrganization(): IntegrationConnection
    {
        $organization = Organization::findOrFail(OrganizationContext::idOrFail());

        return IntegrationConnection::forPlatform($organization, IntegrationPlatform::Facebook);
    }

    public function oauthRedirectUrl(): string
    {
        return route('admin.integrations.facebook.callback');
    }

    public function beginOAuth(): string
    {
        $state = Str::random(40);
        session([
            'meta_oauth_state' => $state,
            'meta_oauth_org_id' => OrganizationContext::idOrFail(),
        ]);

        return $this->graphClient->oauthDialogUrl($this->oauthRedirectUrl(), $state);
    }

    public function handleOAuthCallback(string $code, string $state): IntegrationConnection
    {
        if ($state !== session('meta_oauth_state')) {
            abort(403, 'Invalid OAuth state.');
        }

        $organizationId = (int) session('meta_oauth_org_id');
        if (! $organizationId) {
            abort(403, 'Missing organization context for Facebook OAuth.');
        }

        session()->forget(['meta_oauth_state', 'meta_oauth_org_id']);

        $tokenPayload = $this->graphClient->exchangeCodeForToken($code, $this->oauthRedirectUrl());
        $shortToken = $tokenPayload['access_token'] ?? null;

        if (! $shortToken) {
            abort(422, 'Facebook did not return an access token.');
        }

        $longTokenPayload = $this->graphClient->exchangeForLongLivedUserToken($shortToken);
        $userToken = $longTokenPayload['access_token'] ?? $shortToken;
        $expiresIn = $longTokenPayload['expires_in'] ?? $tokenPayload['expires_in'] ?? null;

        $pages = $this->graphClient->listManagedPages($userToken);

        session([
            'meta_oauth_user_token' => $userToken,
            'meta_oauth_user_token_expires_at' => $expiresIn ? now()->addSeconds((int) $expiresIn)->timestamp : null,
            'meta_oauth_pages' => $pages,
            'meta_oauth_org_id' => $organizationId,
        ]);

        $connection = IntegrationConnection::forPlatform(
            Organization::findOrFail($organizationId),
            IntegrationPlatform::Facebook
        );

        $connection->update([
            'status' => IntegrationConnectionStatus::Pending,
            'settings' => array_merge($connection->settings ?? [], [
                'oauth_completed_at' => now()->toIso8601String(),
            ]),
        ]);

        return $connection->fresh();
    }

    public function connectPage(string $pageId, Admin $admin): IntegrationConnection
    {
        $pages = session('meta_oauth_pages', []);
        $selected = collect($pages)->firstWhere('id', $pageId);

        if (! $selected || empty($selected['access_token'])) {
            abort(422, 'Selected Facebook Page was not found in your OAuth session. Please reconnect.');
        }

        $organizationId = (int) session('meta_oauth_org_id', OrganizationContext::idOrFail());
        $organization = Organization::findOrFail($organizationId);

        return DB::transaction(function () use ($selected, $admin, $organization) {
            $connection = IntegrationConnection::forPlatform($organization, IntegrationPlatform::Facebook);

            $connection->update([
                'status' => IntegrationConnectionStatus::Connected,
                'external_account_id' => (string) $selected['id'],
                'external_account_name' => (string) ($selected['name'] ?? 'Facebook Page'),
                'access_token' => (string) $selected['access_token'],
                'token_expires_at' => null,
                'connected_by' => $admin->id,
                'settings' => array_merge($connection->settings ?? [], [
                    'connected_at' => now()->toIso8601String(),
                ]),
            ]);

            try {
                $subscribed = $this->graphClient->subscribePageToLeadgen(
                    $connection->external_account_id,
                    $connection->access_token
                );

                if ($subscribed) {
                    $connection->update(['webhook_subscribed_at' => now()]);
                }
            } catch (\Throwable) {
                // Webhook may already be subscribed manually in Meta Developer App.
                // Keep the Page connection; leadgen can still arrive via app-level webhook.
            }

            session()->forget(['meta_oauth_user_token', 'meta_oauth_pages', 'meta_oauth_org_id']);

            return $connection->fresh();
        });
    }

    public function disconnect(IntegrationConnection $connection): void
    {
        if ($connection->external_account_id && $connection->access_token) {
            try {
                $this->graphClient->unsubscribePageFromLeadgen(
                    $connection->external_account_id,
                    $connection->access_token
                );
            } catch (\Throwable) {
                // Best-effort unsubscribe; local disconnect still proceeds.
            }
        }

        $connection->update([
            'status' => IntegrationConnectionStatus::Disconnected,
            'external_account_id' => null,
            'external_account_name' => null,
            'access_token' => null,
            'token_expires_at' => null,
            'webhook_subscribed_at' => null,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function syncLeadForms(IntegrationConnection $connection): array
    {
        if (! $connection->isConnected()) {
            return [];
        }

        $forms = $this->graphClient->listLeadForms(
            $connection->external_account_id,
            $connection->access_token
        );

        foreach ($forms as $form) {
            IntegrationFormMapping::updateOrCreate(
                [
                    'organization_id' => $connection->organization_id,
                    'external_form_id' => (string) $form['id'],
                ],
                [
                    'integration_connection_id' => $connection->id,
                    'external_form_name' => (string) ($form['name'] ?? 'Facebook Form'),
                    'internal_label' => (string) ($form['name'] ?? 'Facebook Form'),
                    'lead_source_label' => 'Facebook — '.($form['name'] ?? 'Lead Ad'),
                    'is_active' => true,
                ]
            );
        }

        return $forms;
    }

    public function availablePagesFromSession(): array
    {
        return session('meta_oauth_pages', []);
    }

    public function hasPendingPageSelection(): bool
    {
        return filled(session('meta_oauth_pages'));
    }
}
