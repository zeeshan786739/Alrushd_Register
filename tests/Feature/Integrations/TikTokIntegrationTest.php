<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\TikTokLeadSubmissionStatus;
use App\Models\Admin;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Models\Organization;
use App\Services\Integrations\TikTok\TikTokIntegrationService;
use Database\Seeders\CrmPermissionsSeeder;
use Database\Seeders\IntegrationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TikTokIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organizationA;

    protected Organization $organizationB;

    protected Admin $adminA;

    protected Admin $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CrmPermissionsSeeder::class);
        $this->seed(IntegrationPermissionsSeeder::class);

        $this->organizationA = Organization::create(['name' => 'Org A', 'slug' => 'org-a', 'is_active' => true]);
        $this->organizationB = Organization::create(['name' => 'Org B', 'slug' => 'org-b', 'is_active' => true]);

        $role = Role::where('name', 'super-admin')->where('guard_name', 'admin')->firstOrFail();

        $this->adminA = Admin::create([
            'name' => 'Admin A',
            'email' => 'admin-a@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $this->adminA->assignRole($role);

        $this->adminB = Admin::create([
            'name' => 'Admin B',
            'email' => 'admin-b@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationB->id,
        ]);
        $this->adminB->assignRole($role);

        Http::preventStrayRequests();
    }

    public function test_guest_is_redirected_from_tiktok_page(): void
    {
        $this->get(route('admin.integrations.tiktok.show'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_tiktok_page_without_creating_a_connection(): void
    {
        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('TikTok Lead Ads')
            ->assertSee('Not Connected')
            ->assertSee('Connect TikTok')
            ->assertSee('No TikTok leads received yet')
            ->assertDontSee('Set up Facebook first');

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);
    }

    public function test_view_only_admin_cannot_connect_tiktok(): void
    {
        $role = Role::create(['name' => 'tiktok-viewer', 'guard_name' => 'admin']);
        $role->givePermissionTo(Permission::findByName('view integrations', 'admin'));

        $viewer = Admin::create([
            'name' => 'Viewer',
            'email' => 'tiktok-viewer@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $viewer->assignRole($role);

        $this->actingAs($viewer, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertDontSee(route('admin.integrations.tiktok.connect'), false);

        $this->actingAs($viewer, 'admin')
            ->get(route('admin.integrations.tiktok.connect'))
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'code',
                'state' => 'state',
            ]))
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '123',
            ])
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->post(route('admin.integrations.tiktok.sync-forms'))
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->post(route('admin.integrations.tiktok.register-webhook'))
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->post(route('admin.integrations.tiktok.reprocess-pending'))
            ->assertForbidden();
    }

    public function test_connect_without_credentials_redirects_with_message(): void
    {
        config([
            'integrations.tiktok.app_id' => null,
            'integrations.tiktok.app_secret' => null,
        ]);

        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.connect'))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok API credentials have not been configured yet.');

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);

        Http::assertNothingSent();
    }

    public function test_connect_generates_secure_oauth_state_and_redirects_to_tiktok(): void
    {
        $this->configureTikTokCredentials();
        Http::fake();

        $response = $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.connect'));

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://business-api.tiktok.com/portal/auth?', $location);
        $this->assertStringContainsString('app_id=tiktok-test-app', $location);
        $this->assertStringContainsString('state=', $location);
        $this->assertStringNotContainsString('tiktok-test-secret', $location);
        $this->assertStringNotContainsString('secret=', $location);

        $state = session(TikTokIntegrationService::SESSION_STATE);
        $this->assertIsString($state);
        $this->assertSame(64, strlen($state));
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $state);
        $this->assertSame($this->organizationA->id, (int) session(TikTokIntegrationService::SESSION_ORG_ID));
        $this->assertStringContainsString('state='.$state, $location);

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);

        Http::assertNothingSent();
    }

    public function test_callback_rejects_missing_state(): void
    {
        $this->configureTikTokCredentials();
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'valid-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'auth-code',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok authorization was incomplete. Please connect again.');

        Http::assertNothingSent();
        $this->assertNull(session(TikTokIntegrationService::SESSION_STATE));
    }

    public function test_callback_rejects_incorrect_state(): void
    {
        $this->configureTikTokCredentials();
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'expected-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'auth-code',
                'state' => 'wrong-state',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok authorization could not be verified. Please connect again.');

        Http::assertNothingSent();
        $this->assertNull(session(TikTokIntegrationService::SESSION_ACCESS_TOKEN));
    }

    public function test_callback_cannot_cross_organizations(): void
    {
        $this->configureTikTokCredentials();
        Http::fake();

        $this->actingAs($this->adminB, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'shared-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'auth-code',
                'state' => 'shared-state',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok authorization does not belong to this organization. Please connect again.');

        Http::assertNothingSent();
        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationB->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);
        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);
    }

    public function test_tiktok_api_errors_are_handled_safely(): void
    {
        $this->configureTikTokCredentials();
        Http::fake([
            'https://business-api.tiktok.com/open_api/v1.3/oauth2/access_token/' => Http::response([
                'code' => 40100,
                'message' => 'Invalid auth_code',
                'request_id' => 'req-1',
            ], 200),
        ]);

        $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'valid-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'bad-code',
                'state' => 'valid-state',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok could not complete authorization. Please try connecting again.')
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ACCESS_TOKEN);

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);
    }

    public function test_http_failures_do_not_expose_app_secret_in_user_or_exception_output(): void
    {
        $this->configureTikTokCredentials();
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException(
                'cURL error 28 for GET https://business-api.tiktok.com/open_api/v1.3/oauth2/advertiser/get/?app_id=tiktok-test-app&secret=tiktok-test-secret'
            );
        });

        $response = $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'valid-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'good-code',
                'state' => 'valid-state',
            ]));

        $response
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok could not complete authorization. Please try connecting again.');

        $this->assertStringNotContainsString('tiktok-test-secret', (string) $response->headers->get('Location'));
        $this->assertStringNotContainsString('tiktok-test-secret', json_encode(session()->all()) ?: '');
        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);
    }

    public function test_successful_oauth_stores_temporary_authorized_accounts(): void
    {
        $this->configureTikTokCredentials();
        $this->fakeSuccessfulTikTokOAuth();

        $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'valid-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'auth_code' => 'good-code',
                'state' => 'valid-state',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success')
            ->assertSessionHas(TikTokIntegrationService::SESSION_ADVERTISERS, [
                ['id' => '1111111111', 'name' => 'School Ads Account'],
                ['id' => '2222222222', 'name' => 'Second Advertiser'],
            ])
            ->assertSessionMissing(TikTokIntegrationService::SESSION_STATE);

        $sessionToken = session(TikTokIntegrationService::SESSION_ACCESS_TOKEN);
        $this->assertIsString($sessionToken);
        $this->assertNotSame('tiktok-access-token', $sessionToken);
        $this->assertSame('tiktok-access-token', Crypt::decryptString($sessionToken));

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Select TikTok Ads Account')
            ->assertSee('School Ads Account')
            ->assertSee('1111111111')
            ->assertSee('Connect This Account')
            ->assertDontSee('tiktok-access-token');
    }

    public function test_arbitrary_advertiser_id_cannot_be_selected(): void
    {
        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '9999999999',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'The selected TikTok Ads account was not part of this authorization. Please connect again.');

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);
    }

    public function test_authorized_advertiser_can_be_selected_and_saved_for_current_organization(): void
    {
        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success', 'TikTok Ads account connected for this organization.')
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ACCESS_TOKEN)
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ADVERTISERS)
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ORG_ID)
            ->assertSessionMissing(TikTokIntegrationService::SESSION_STATE);

        $this->assertDatabaseHas('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
            'status' => IntegrationConnectionStatus::Connected->value,
            'external_account_id' => '1111111111',
            'external_account_name' => 'School Ads Account',
            'connected_by' => $this->adminA->id,
        ]);

        $this->assertDatabaseMissing('integration_connections', [
            'organization_id' => $this->organizationB->id,
            'platform' => IntegrationPlatform::TikTok->value,
        ]);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Connected')
            ->assertSee('School Ads Account')
            ->assertSee('1111111111')
            ->assertSee('Waiting for sync')
            ->assertDontSee('tiktok-access-token');
    }

    public function test_token_uses_existing_encrypted_storage_behavior(): void
    {
        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $raw = DB::table('integration_connections')
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok->value)
            ->value('access_token');

        $this->assertNotSame('tiktok-access-token', $raw);
        $this->assertNotEmpty($raw);

        $connection = IntegrationConnection::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok)
            ->first();

        $this->assertSame('tiktok-access-token', $connection?->access_token);
    }

    public function test_existing_facebook_connection_is_unaffected(): void
    {
        $facebook = IntegrationConnection::create([
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::Facebook,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'page-1',
            'external_account_name' => 'Org A Page',
            'access_token' => 'facebook-token',
            'connected_by' => $this->adminA->id,
        ]);

        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $facebook->refresh();
        $this->assertSame('page-1', $facebook->external_account_id);
        $this->assertSame('Org A Page', $facebook->external_account_name);
        $this->assertSame('facebook-token', $facebook->access_token);
        $this->assertSame(IntegrationPlatform::Facebook, $facebook->platform);
        $this->assertSame(IntegrationConnectionStatus::Connected, $facebook->status);
    }

    public function test_failed_oauth_does_not_overwrite_an_existing_tiktok_connection(): void
    {
        $existing = IntegrationConnection::create([
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'existing-advertiser',
            'external_account_name' => 'Existing Advertiser',
            'access_token' => 'existing-token',
            'connected_by' => $this->adminA->id,
        ]);

        $this->configureTikTokCredentials();
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_STATE => 'valid-state',
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            ])
            ->get(route('admin.integrations.tiktok.callback', [
                'error' => 'access_denied',
                'state' => 'valid-state',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok authorization was cancelled or denied.');

        $existing->refresh();
        $this->assertSame('existing-advertiser', $existing->external_account_id);
        $this->assertSame('Existing Advertiser', $existing->external_account_name);
        $this->assertSame('existing-token', $existing->access_token);
        $this->assertSame(IntegrationConnectionStatus::Connected, $existing->status);
        $this->assertSame(1, IntegrationConnection::query()->where('platform', IntegrationPlatform::TikTok)->count());
    }

    public function test_oauth_session_data_is_cleaned_after_successful_selection(): void
    {
        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession() + ['unrelated_session_key' => 'keep-me'])
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('unrelated_session_key', 'keep-me')
            ->assertSessionMissing(TikTokIntegrationService::SESSION_STATE)
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ORG_ID)
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ACCESS_TOKEN)
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ADVERTISERS);
    }

    public function test_reconnect_updates_only_this_organization_tiktok_row(): void
    {
        IntegrationConnection::create([
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'old-advertiser',
            'external_account_name' => 'Old Advertiser',
            'access_token' => 'old-token',
            'connected_by' => $this->adminA->id,
        ]);

        IntegrationConnection::create([
            'organization_id' => $this->organizationB->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'org-b-advertiser',
            'external_account_name' => 'Org B Advertiser',
            'access_token' => 'org-b-token',
            'connected_by' => $this->adminB->id,
        ]);

        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $this->assertSame(1, IntegrationConnection::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok)
            ->count());

        $this->assertDatabaseHas('integration_connections', [
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok->value,
            'external_account_id' => '1111111111',
            'external_account_name' => 'School Ads Account',
        ]);

        $this->assertDatabaseHas('integration_connections', [
            'organization_id' => $this->organizationB->id,
            'platform' => IntegrationPlatform::TikTok->value,
            'external_account_id' => 'org-b-advertiser',
            'external_account_name' => 'Org B Advertiser',
        ]);
    }

    public function test_reconnect_same_advertiser_keeps_valid_subscription_state(): void
    {
        IntegrationConnection::create([
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => '1111111111',
            'external_account_name' => 'School Ads Account',
            'access_token' => 'old-token',
            'connected_by' => $this->adminA->id,
            'webhook_subscribed_at' => now()->subHour(),
            'settings' => [
                'subscription_id' => 'sub-keep',
                'subscription_advertiser_id' => '1111111111',
                'forms_last_synced_at' => '2026-08-01T00:00:00+00:00',
            ],
        ]);

        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $connection = IntegrationConnection::query()
            ->where('organization_id', $this->organizationA->id)
            ->where('platform', IntegrationPlatform::TikTok)
            ->firstOrFail();

        $this->assertSame('1111111111', $connection->external_account_id);
        $this->assertSame('sub-keep', $connection->settings['subscription_id'] ?? null);
        $this->assertSame('1111111111', $connection->settings['subscription_advertiser_id'] ?? null);
        $this->assertSame('2026-08-01T00:00:00+00:00', $connection->settings['forms_last_synced_at'] ?? null);
        $this->assertNotNull($connection->webhook_subscribed_at);
        $this->assertSame('tiktok-access-token', $connection->access_token);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Subscribed')
            ->assertDontSee('Setup required');
    }

    public function test_reconnect_different_advertiser_clears_stale_subscription_and_keeps_history(): void
    {
        $connection = IntegrationConnection::create([
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => '9999999999',
            'external_account_name' => 'Old Advertiser',
            'access_token' => 'old-token',
            'connected_by' => $this->adminA->id,
            'webhook_subscribed_at' => now()->subHour(),
            'settings' => [
                'subscription_id' => 'sub-old',
                'subscription_advertiser_id' => '9999999999',
                'lead_webhook_callback_url' => 'https://example.test/webhooks/tiktok/leads',
                'forms_last_synced_at' => '2026-08-01T00:00:00+00:00',
            ],
        ]);

        $submission = TikTokLeadSubmission::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '9999999999',
            'tiktok_lead_id' => 'lead-history',
            'status' => TikTokLeadSubmissionStatus::Processed,
            'field_data' => ['name' => 'Historical Lead'],
            'received_at' => now()->subDay(),
            'processed_at' => now()->subDay(),
        ]);

        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession($this->authorizedSession())
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $connection->refresh();
        $this->assertSame('1111111111', $connection->external_account_id);
        $this->assertSame('School Ads Account', $connection->external_account_name);
        $this->assertNull($connection->webhook_subscribed_at);
        $this->assertArrayNotHasKey('subscription_id', $connection->settings ?? []);
        $this->assertArrayNotHasKey('subscription_advertiser_id', $connection->settings ?? []);
        $this->assertArrayNotHasKey('lead_webhook_callback_url', $connection->settings ?? []);
        $this->assertSame('2026-08-01T00:00:00+00:00', $connection->settings['forms_last_synced_at'] ?? null);

        $this->assertDatabaseHas('tiktok_lead_submissions', [
            'id' => $submission->id,
            'advertiser_id' => '9999999999',
            'tiktok_lead_id' => 'lead-history',
            'status' => TikTokLeadSubmissionStatus::Processed->value,
        ]);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Setup required')
            ->assertSee('Enable lead delivery')
            ->assertSee('Historical Lead')
            ->assertDontSee('Subscribed');
    }

    public function test_corrupted_oauth_session_token_fails_safely_without_overwriting_connection(): void
    {
        $existing = IntegrationConnection::create([
            'organization_id' => $this->organizationA->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'existing-advertiser',
            'external_account_name' => 'Existing Advertiser',
            'access_token' => 'existing-token',
            'connected_by' => $this->adminA->id,
        ]);

        $this->configureTikTokCredentials();

        $this->actingAs($this->adminA, 'admin')
            ->withSession([
                TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
                TikTokIntegrationService::SESSION_ACCESS_TOKEN => 'not-a-valid-encrypted-token',
                TikTokIntegrationService::SESSION_ADVERTISERS => [
                    ['id' => '1111111111', 'name' => 'School Ads Account'],
                ],
            ])
            ->post(route('admin.integrations.tiktok.select-advertiser'), [
                'advertiser_id' => '1111111111',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'Your TikTok authorization session has expired. Please connect again.')
            ->assertSessionMissing(TikTokIntegrationService::SESSION_ACCESS_TOKEN);

        $existing->refresh();
        $this->assertSame('existing-advertiser', $existing->external_account_id);
        $this->assertSame('Existing Advertiser', $existing->external_account_name);
        $this->assertSame('existing-token', $existing->access_token);
    }

    public function test_integration_hub_does_not_show_tiktok_soon(): void
    {
        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.hub'))
            ->assertOk()
            ->assertDontSee('TikTok (soon)')
            ->assertSee('Set up TikTok')
            ->assertSee('Connect Facebook');
    }

    public function test_tiktok_page_does_not_show_another_organization_connection(): void
    {
        IntegrationConnection::create([
            'organization_id' => $this->organizationB->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => 'advertiser-b',
            'external_account_name' => 'Org B Advertiser',
            'access_token' => 'token-b',
        ]);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Not Connected')
            ->assertDontSee('Org B Advertiser');
    }

    private function configureTikTokCredentials(): void
    {
        config([
            'integrations.tiktok.app_id' => 'tiktok-test-app',
            'integrations.tiktok.app_secret' => 'tiktok-test-secret',
            'integrations.tiktok.redirect_uri' => 'https://example.test/admin/integrations/tiktok/callback',
            'integrations.tiktok.auth_url' => 'https://business-api.tiktok.com/portal/auth',
            'integrations.tiktok.api_base' => 'https://business-api.tiktok.com/open_api/v1.3',
        ]);
    }

    private function fakeSuccessfulTikTokOAuth(): void
    {
        Http::preventStrayRequests();
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            if (str_contains($request->url(), '/oauth2/access_token/')) {
                $this->assertSame('tiktok-test-app', $request['app_id']);
                $this->assertSame('good-code', $request['auth_code']);
                $this->assertArrayHasKey('secret', $request->data());

                return Http::response([
                    'code' => 0,
                    'message' => 'OK',
                    'data' => [
                        'access_token' => 'tiktok-access-token',
                        'advertiser_ids' => ['1111111111', '2222222222'],
                        'scope' => [1, 2],
                    ],
                    'request_id' => 'req-ok',
                ], 200);
            }

            if (str_contains($request->url(), '/oauth2/advertiser/get/')) {
                $this->assertSame('tiktok-access-token', $request->header('Access-Token')[0] ?? null);

                return Http::response([
                    'code' => 0,
                    'message' => 'OK',
                    'data' => [
                        'list' => [
                            ['advertiser_id' => '1111111111', 'advertiser_name' => 'School Ads Account'],
                            ['advertiser_id' => '2222222222', 'advertiser_name' => 'Second Advertiser'],
                        ],
                    ],
                    'request_id' => 'req-adv',
                ], 200);
            }

            return Http::response(['code' => 1, 'message' => 'Unexpected TikTok request'], 500);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizedSession(): array
    {
        return [
            TikTokIntegrationService::SESSION_ORG_ID => $this->organizationA->id,
            TikTokIntegrationService::SESSION_ACCESS_TOKEN => Crypt::encryptString('tiktok-access-token'),
            TikTokIntegrationService::SESSION_ADVERTISERS => [
                ['id' => '1111111111', 'name' => 'School Ads Account'],
                ['id' => '2222222222', 'name' => 'Second Advertiser'],
            ],
        ];
    }
}
