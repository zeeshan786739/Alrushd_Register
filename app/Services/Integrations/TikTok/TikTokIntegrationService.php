<?php

namespace App\Services\Integrations\TikTok;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\TikTokLeadSubmissionStatus;
use App\Jobs\Integrations\ProcessTikTokLeadJob;
use App\Models\Admin;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\TikTokFormMapping;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Support\OrganizationContext;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TikTokIntegrationService
{
    public const SESSION_STATE = 'tiktok_oauth_state';

    public const SESSION_ORG_ID = 'tiktok_oauth_org_id';

    public const SESSION_ACCESS_TOKEN = 'tiktok_oauth_access_token';

    public const SESSION_ADVERTISERS = 'tiktok_oauth_advertisers';

    public function __construct(
        private TikTokApiClient $apiClient,
    ) {}

    /**
     * Read-only lookup of this organization's TikTok connection.
     * Never creates a row and never falls back to another organization.
     */
    public function connectionForCurrentOrganization(): ?IntegrationConnection
    {
        return IntegrationConnection::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->where('platform', IntegrationPlatform::TikTok)
            ->first();
    }

    public function credentialsConfigured(): bool
    {
        return filled(config('integrations.tiktok.app_id'))
            && filled(config('integrations.tiktok.app_secret'));
    }

    public function oauthRedirectUrl(): string
    {
        $configured = config('integrations.tiktok.redirect_uri');

        if (filled($configured)) {
            return (string) $configured;
        }

        return route('admin.integrations.tiktok.callback');
    }

    public function beginOAuth(): string
    {
        $this->forgetOAuthSession();

        $state = bin2hex(random_bytes(32));

        session([
            self::SESSION_STATE => $state,
            self::SESSION_ORG_ID => OrganizationContext::idOrFail(),
        ]);

        return $this->apiClient->authorizationUrl($state, $this->oauthRedirectUrl());
    }

    public function handleOAuthCallback(string $authCode, string $state): void
    {
        $this->assertValidOAuthState($state);
        $organizationId = $this->assertCurrentOrganizationMatchesOAuthSession();

        session()->forget([self::SESSION_STATE]);

        $tokenResult = $this->apiClient->exchangeAuthCodeForToken($authCode);
        $advertisers = $this->apiClient->listAuthorizedAdvertisers($tokenResult['access_token']);

        if ($advertisers === []) {
            $advertisers = $this->advertisersFromTokenIds($tokenResult['advertiser_ids']);
        }

        if ($advertisers === []) {
            $this->forgetOAuthSession();

            throw TikTokApiException::noAdvertisers();
        }

        session([
            self::SESSION_ORG_ID => $organizationId,
            self::SESSION_ACCESS_TOKEN => $this->encryptOAuthAccessToken($tokenResult['access_token']),
            self::SESSION_ADVERTISERS => $advertisers,
        ]);
    }

    public function selectAdvertiser(string $advertiserId, Admin $admin): IntegrationConnection
    {
        $organizationId = $this->assertCurrentOrganizationMatchesOAuthSession();

        if ($admin->organization_id !== $organizationId) {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('TikTok authorization does not belong to this organization. Please connect again.');
        }

        $encryptedAccessToken = session(self::SESSION_ACCESS_TOKEN);
        if (! is_string($encryptedAccessToken) || $encryptedAccessToken === '') {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('Your TikTok authorization session has expired. Please connect again.');
        }

        $accessToken = $this->decryptOAuthAccessToken($encryptedAccessToken);

        $selected = $this->advertiserFromAuthorizedSession($advertiserId);
        if ($selected === null) {
            throw new TikTokOAuthException('The selected TikTok Ads account was not part of this authorization. Please connect again.');
        }

        return DB::transaction(function () use ($organizationId, $admin, $selected, $accessToken) {
            $connection = IntegrationConnection::query()
                ->where('organization_id', $organizationId)
                ->where('platform', IntegrationPlatform::TikTok)
                ->lockForUpdate()
                ->first();

            $settings = is_array($connection?->settings) ? $connection->settings : [];
            $advertiserChanged = $connection !== null
                && filled($connection->external_account_id)
                && ! hash_equals((string) $connection->external_account_id, $selected['id']);

            if ($advertiserChanged) {
                unset(
                    $settings['subscription_id'],
                    $settings['subscription_advertiser_id'],
                    $settings['lead_webhook_callback_url'],
                );
            }

            $settings['connected_at'] = now()->toIso8601String();

            $attributes = [
                'status' => IntegrationConnectionStatus::Connected,
                'external_account_id' => $selected['id'],
                'external_account_name' => $selected['name'],
                'access_token' => $accessToken,
                'token_expires_at' => null,
                'connected_by' => $admin->id,
                'settings' => $settings,
            ];

            if ($advertiserChanged) {
                $attributes['webhook_subscribed_at'] = null;
            }

            if ($connection) {
                $connection->update($attributes);
            } else {
                $connection = IntegrationConnection::query()->create([
                    'organization_id' => $organizationId,
                    'platform' => IntegrationPlatform::TikTok,
                    ...$attributes,
                ]);
            }

            $this->forgetOAuthSession();

            return $connection->fresh();
        });
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function pendingAdvertisersFromSession(): array
    {
        if (! $this->hasPendingAdvertiserSelection()) {
            return [];
        }

        $advertisers = session(self::SESSION_ADVERTISERS, []);

        return is_array($advertisers) ? array_values($advertisers) : [];
    }

    public function hasPendingAdvertiserSelection(): bool
    {
        try {
            $this->assertCurrentOrganizationMatchesOAuthSession();
        } catch (TikTokOAuthException) {
            return false;
        }

        $token = session(self::SESSION_ACCESS_TOKEN);
        $advertisers = session(self::SESSION_ADVERTISERS, []);

        return is_string($token)
            && $token !== ''
            && is_array($advertisers)
            && $advertisers !== [];
    }

    public function forgetOAuthSession(): void
    {
        session()->forget([
            self::SESSION_STATE,
            self::SESSION_ORG_ID,
            self::SESSION_ACCESS_TOKEN,
            self::SESSION_ADVERTISERS,
        ]);
    }

    public function forgetOAuthStartSession(): void
    {
        session()->forget([
            self::SESSION_STATE,
            self::SESSION_ORG_ID,
        ]);
    }

    private function assertValidOAuthState(string $state): void
    {
        $sessionState = session(self::SESSION_STATE);

        if (! is_string($sessionState) || $sessionState === '' || $state === '') {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('TikTok authorization expired or is missing. Please connect again.');
        }

        if (! hash_equals($sessionState, $state)) {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('TikTok authorization could not be verified. Please connect again.');
        }
    }

    private function assertCurrentOrganizationMatchesOAuthSession(): int
    {
        $currentOrganizationId = OrganizationContext::idOrFail();
        $sessionOrganizationId = (int) session(self::SESSION_ORG_ID);

        if ($sessionOrganizationId <= 0 || $sessionOrganizationId !== $currentOrganizationId) {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('TikTok authorization does not belong to this organization. Please connect again.');
        }

        return $currentOrganizationId;
    }

    /**
     * @return array{id: string, name: string}|null
     */
    private function advertiserFromAuthorizedSession(string $advertiserId): ?array
    {
        $advertisers = session(self::SESSION_ADVERTISERS, []);
        if (! is_array($advertisers) || $advertiserId === '') {
            return null;
        }

        foreach ($advertisers as $advertiser) {
            if (! is_array($advertiser)) {
                continue;
            }

            $id = isset($advertiser['id']) ? (string) $advertiser['id'] : '';
            if ($id !== '' && hash_equals($id, $advertiserId)) {
                $name = isset($advertiser['name']) ? trim((string) $advertiser['name']) : '';

                return [
                    'id' => $id,
                    'name' => $name !== '' ? $name : $id,
                ];
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $advertiserIds
     * @return array<int, array{id: string, name: string}>
     */
    private function advertisersFromTokenIds(array $advertiserIds): array
    {
        $advertisers = [];

        foreach ($advertiserIds as $id) {
            $id = trim((string) $id);
            if ($id === '') {
                continue;
            }

            $advertisers[] = [
                'id' => $id,
                'name' => $id,
            ];
        }

        return $advertisers;
    }

    /**
     * @return Collection<int, TikTokFormMapping>
     */
    public function formMappingsForCurrentOrganization(): Collection
    {
        $organizationId = OrganizationContext::idOrFail();
        $connection = $this->connectionForCurrentOrganization();

        if (! $connection?->isConnected()) {
            return collect();
        }

        return TikTokFormMapping::query()
            ->where('organization_id', $organizationId)
            ->where('integration_connection_id', $connection->id)
            ->where('advertiser_id', $connection->external_account_id)
            ->with('assignedAdmin')
            ->orderBy('external_form_name')
            ->get();
    }

    public function requireConnectedConnection(): IntegrationConnection
    {
        $connection = $this->connectionForCurrentOrganization();

        if (! $connection?->isConnected() || ! filled($connection->external_account_id) || ! filled($connection->access_token)) {
            throw new TikTokOAuthException('Connect a TikTok Ads account before syncing Lead Forms.');
        }

        return $connection;
    }

    public function mappingForCurrentOrganization(TikTokFormMapping $mapping): TikTokFormMapping
    {
        $connection = $this->requireConnectedConnection();
        $organizationId = OrganizationContext::idOrFail();

        if (
            (int) $mapping->organization_id !== $organizationId
            || (int) $mapping->integration_connection_id !== (int) $connection->id
            || ! hash_equals((string) $connection->external_account_id, (string) $mapping->advertiser_id)
        ) {
            abort(404);
        }

        return $mapping;
    }

    /**
     * @return Collection<int, TikTokFormMapping>
     */
    public function syncInstantForms(): Collection
    {
        $connection = $this->requireConnectedConnection();
        $organizationId = (int) $connection->organization_id;
        $advertiserId = (string) $connection->external_account_id;

        $forms = $this->apiClient->listInstantForms($connection->access_token, $advertiserId);

        DB::transaction(function () use ($connection, $organizationId, $advertiserId, $forms) {
            foreach ($forms as $form) {
                $existing = TikTokFormMapping::query()
                    ->where('organization_id', $organizationId)
                    ->where('advertiser_id', $advertiserId)
                    ->where('external_form_id', $form['id'])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $existing->update([
                        'integration_connection_id' => $connection->id,
                        'external_form_name' => $form['name'],
                        'external_status' => $form['status'] !== '' ? $form['status'] : $existing->external_status,
                        'last_synced_at' => now(),
                    ]);

                    continue;
                }

                TikTokFormMapping::query()->create([
                    'organization_id' => $organizationId,
                    'integration_connection_id' => $connection->id,
                    'advertiser_id' => $advertiserId,
                    'external_form_id' => $form['id'],
                    'external_form_name' => $form['name'],
                    'external_status' => $form['status'] !== '' ? $form['status'] : null,
                    'lead_source_label' => 'TikTok — '.$form['name'],
                    'priority' => 'medium',
                    'auto_create_lead' => true,
                    'is_active' => true,
                    'field_mapping' => [],
                    'external_fields' => [],
                    'last_synced_at' => now(),
                ]);
            }

            $settings = is_array($connection->settings) ? $connection->settings : [];
            $settings['forms_last_synced_at'] = now()->toIso8601String();
            $connection->update(['settings' => $settings]);
        });

        return $this->formMappingsForCurrentOrganization();
    }

    /**
     * @return array<int, array{id: string, label: string, suggested: ?string, mapped_to: string}>
     */
    public function fieldsForMapping(TikTokFormMapping $mapping): array
    {
        $mapping = $this->mappingForCurrentOrganization($mapping);
        $connection = $this->requireConnectedConnection();

        $fields = $this->apiClient->getInstantFormFields(
            $connection->access_token,
            (string) $connection->external_account_id,
            $mapping->external_form_id
        );

        $savedMapping = is_array($mapping->field_mapping) ? $mapping->field_mapping : [];
        $savedFieldIds = is_array($mapping->external_fields) ? $mapping->external_fields : [];

        $fields = $this->mergeStoredFields($fields, $savedFieldIds, $savedMapping);
        $fieldIds = array_map(fn (array $field) => $field['id'], $fields);

        $mapping->update(['external_fields' => $fieldIds]);

        return $this->mappingFieldRows($fields, $savedMapping);
    }

    /**
     * Build mapping rows from last saved TikTok fields when the live API is unavailable.
     *
     * @return array<int, array{id: string, label: string, suggested: ?string, mapped_to: string}>
     */
    public function fieldsFromStoredMapping(TikTokFormMapping $mapping): array
    {
        $mapping = $this->mappingForCurrentOrganization($mapping);
        $savedMapping = is_array($mapping->field_mapping) ? $mapping->field_mapping : [];
        $savedFieldIds = is_array($mapping->external_fields) ? $mapping->external_fields : [];
        $fields = $this->mergeStoredFields([], $savedFieldIds, $savedMapping);

        return $this->mappingFieldRows($fields, $savedMapping);
    }

    /**
     * @param  array<int, array{id: string, label: string}>  $fields
     * @param  array<int, mixed>  $savedFieldIds
     * @param  array<string, mixed>  $savedMapping
     * @return array<int, array{id: string, label: string}>
     */
    private function mergeStoredFields(array $fields, array $savedFieldIds, array $savedMapping): array
    {
        $fieldIds = [];

        foreach ($fields as $field) {
            $fieldIds[] = $field['id'];
        }

        foreach (array_merge($savedFieldIds, array_keys($savedMapping)) as $savedId) {
            if (! is_string($savedId) || $savedId === '' || in_array($savedId, $fieldIds, true)) {
                continue;
            }

            $fields[] = [
                'id' => $savedId,
                'label' => $savedId,
            ];
            $fieldIds[] = $savedId;
        }

        return $fields;
    }

    /**
     * @param  array<int, array{id: string, label: string}>  $fields
     * @param  array<string, mixed>  $savedMapping
     * @return array<int, array{id: string, label: string, suggested: ?string, mapped_to: string}>
     */
    private function mappingFieldRows(array $fields, array $savedMapping): array
    {
        $rows = [];

        foreach ($fields as $field) {
            $current = $savedMapping[$field['id']] ?? null;
            $suggested = TikTokCrmFields::suggest($field['id']);
            $mappedTo = is_string($current) ? $current : ($suggested ?? '');

            if (is_string($mappedTo) && $mappedTo !== '' && ! TikTokCrmFields::isAllowed($mappedTo)) {
                $mappedTo = '';
            }

            $rows[] = [
                'id' => $field['id'],
                'label' => $field['label'],
                'suggested' => $suggested,
                'mapped_to' => $mappedTo,
            ];
        }

        return $rows;
    }

    public function updateFormMapping(TikTokFormMapping $mapping, array $payload): TikTokFormMapping
    {
        $mapping = $this->mappingForCurrentOrganization($mapping);
        $knownFields = is_array($mapping->external_fields) ? $mapping->external_fields : [];
        $incoming = is_array($payload['field_mapping'] ?? null) ? $payload['field_mapping'] : [];
        $safeMapping = [];

        foreach ($knownFields as $fieldId) {
            if (! is_string($fieldId) || $fieldId === '') {
                continue;
            }

            $safeMapping[$fieldId] = '';
        }

        foreach ($incoming as $tiktokField => $crmField) {
            if (! is_string($tiktokField) || $tiktokField === '') {
                continue;
            }

            if ($knownFields !== [] && ! in_array($tiktokField, $knownFields, true)) {
                continue;
            }

            $safeMapping[$tiktokField] = is_string($crmField) && TikTokCrmFields::isAllowed($crmField)
                ? $crmField
                : '';
        }

        $mapping->update([
            'lead_source_label' => $payload['lead_source_label'],
            'assigned_to' => $payload['assigned_to'],
            'priority' => $payload['priority'],
            'auto_create_lead' => (bool) $payload['auto_create_lead'],
            'is_active' => (bool) $payload['is_active'],
            'field_mapping' => $safeMapping,
        ]);

        return $mapping->fresh(['assignedAdmin']);
    }

    public function formsLastSyncedAt(?IntegrationConnection $connection): ?string
    {
        $timestamp = $connection?->settings['forms_last_synced_at'] ?? null;
        if (! is_string($timestamp) || $timestamp === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($timestamp)->timezone(config('app.timezone'))->diffForHumans();
        } catch (\Throwable) {
            return null;
        }
    }

    public function hasSyncedForms(?IntegrationConnection $connection): bool
    {
        return filled($connection?->settings['forms_last_synced_at'] ?? null)
            || $this->formMappingsForCurrentOrganization()->isNotEmpty();
    }

    public function hasConfiguredFormMapping(): bool
    {
        return $this->formMappingsForCurrentOrganization()
            ->contains(fn (TikTokFormMapping $mapping) => $mapping->mappingStatus() === 'configured');
    }

    public function webhookCallbackUrl(): string
    {
        return url('/webhooks/tiktok/leads');
    }

    public function hasLeadWebhookSubscription(?IntegrationConnection $connection): bool
    {
        if (! $connection?->isConnected()) {
            return false;
        }

        $advertiserId = trim((string) $connection->external_account_id);
        $subscriptionId = trim((string) ($connection->settings['subscription_id'] ?? ''));
        $subscriptionAdvertiserId = trim((string) ($connection->settings['subscription_advertiser_id'] ?? ''));

        if ($advertiserId === '' || $subscriptionId === '' || $subscriptionAdvertiserId === '') {
            return false;
        }

        return hash_equals($advertiserId, $subscriptionAdvertiserId);
    }

    /**
     * @return Collection<int, TikTokLeadSubmission>
     */
    public function recentSubmissionsForCurrentOrganization(int $limit = 25): Collection
    {
        $organizationId = OrganizationContext::idOrFail();
        $connection = $this->connectionForCurrentOrganization();

        if (! $connection) {
            return collect();
        }

        return TikTokLeadSubmission::query()
            ->where('organization_id', $organizationId)
            ->where('integration_connection_id', $connection->id)
            ->with(['formMapping', 'lead'])
            ->latest('received_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function eligibleReprocessCount(): int
    {
        $organizationId = OrganizationContext::idOrFail();
        $connection = $this->connectionForCurrentOrganization();

        if (! $connection) {
            return 0;
        }

        return TikTokLeadSubmission::query()
            ->where('organization_id', $organizationId)
            ->where('integration_connection_id', $connection->id)
            ->whereIn('status', [
                TikTokLeadSubmissionStatus::Pending->value,
                TikTokLeadSubmissionStatus::Failed->value,
                TikTokLeadSubmissionStatus::Unmapped->value,
                TikTokLeadSubmissionStatus::Ignored->value,
            ])
            ->count();
    }

    public function submissionForCurrentOrganization(TikTokLeadSubmission $submission): TikTokLeadSubmission
    {
        $connection = $this->requireConnectedConnection();
        $organizationId = OrganizationContext::idOrFail();

        if (
            (int) $submission->organization_id !== $organizationId
            || (int) $submission->integration_connection_id !== (int) $connection->id
        ) {
            abort(404);
        }

        return $submission;
    }

    public function subscribeCurrentOrganizationWebhooks(): IntegrationConnection
    {
        $connection = $this->requireConnectedConnection();

        if (! $this->credentialsConfigured()) {
            throw new TikTokOAuthException('TikTok API credentials have not been configured yet.');
        }

        $lock = Cache::lock('tiktok:lead-subscription:'.$connection->id, 30);

        if (! $lock->get()) {
            throw new TikTokOAuthException('TikTok lead delivery setup is already in progress. Please try again shortly.');
        }

        try {
            $connection = $connection->fresh();
            if (! $connection?->isConnected() || ! filled($connection->external_account_id) || ! filled($connection->access_token)) {
                throw new TikTokOAuthException('Connect a TikTok Ads account before enabling lead delivery.');
            }

            if ($this->hasLeadWebhookSubscription($connection)) {
                return $connection;
            }

            $result = $this->apiClient->subscribeLeadWebhooks(
                $connection->access_token,
                (string) $connection->external_account_id,
                $this->webhookCallbackUrl()
            );

            $settings = is_array($connection->settings) ? $connection->settings : [];
            $settings['subscription_id'] = $result['subscription_id'];
            $settings['subscription_advertiser_id'] = (string) $connection->external_account_id;
            $settings['lead_webhook_callback_url'] = $this->webhookCallbackUrl();

            $connection->update([
                'settings' => $settings,
                'webhook_subscribed_at' => now(),
            ]);

            return $connection->fresh() ?? $connection;
        } finally {
            $lock->release();
        }
    }

    public function reprocessSubmission(TikTokLeadSubmission $submission): void
    {
        $submission = $this->submissionForCurrentOrganization($submission);

        if (! $submission->status->canReprocess()) {
            throw new TikTokOAuthException('This TikTok lead has already been processed.');
        }

        $submission->update([
            'status' => TikTokLeadSubmissionStatus::Pending,
            'error_message' => null,
        ]);

        ProcessTikTokLeadJob::dispatch($submission->id);
    }

    public function reprocessPendingSubmissions(): int
    {
        $connection = $this->requireConnectedConnection();
        $organizationId = OrganizationContext::idOrFail();

        $submissions = TikTokLeadSubmission::query()
            ->where('organization_id', $organizationId)
            ->where('integration_connection_id', $connection->id)
            ->whereIn('status', [
                TikTokLeadSubmissionStatus::Pending->value,
                TikTokLeadSubmissionStatus::Failed->value,
                TikTokLeadSubmissionStatus::Unmapped->value,
                TikTokLeadSubmissionStatus::Ignored->value,
            ])
            ->orderBy('id')
            ->limit(50)
            ->get();

        foreach ($submissions as $submission) {
            $submission->update([
                'status' => TikTokLeadSubmissionStatus::Pending,
                'error_message' => null,
            ]);
            ProcessTikTokLeadJob::dispatch($submission->id);
        }

        return $submissions->count();
    }

    private function encryptOAuthAccessToken(string $token): string
    {
        return Crypt::encryptString($token);
    }

    private function decryptOAuthAccessToken(string $encrypted): string
    {
        try {
            $token = Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('Your TikTok authorization session has expired. Please connect again.');
        }

        if ($token === '') {
            $this->forgetOAuthSession();

            throw new TikTokOAuthException('Your TikTok authorization session has expired. Please connect again.');
        }

        return $token;
    }
}
