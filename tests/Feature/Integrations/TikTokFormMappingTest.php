<?php

namespace Tests\Feature\Integrations;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Models\Admin;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\IntegrationFormMapping;
use App\Models\Integrations\TikTokFormMapping;
use App\Models\Organization;
use App\Services\Integrations\TikTok\TikTokApiClient;
use Database\Seeders\CrmPermissionsSeeder;
use Database\Seeders\IntegrationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TikTokFormMappingTest extends TestCase
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
            'name' => 'Admissions Manager',
            'email' => 'admin-a-forms@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $this->adminA->assignRole($role);

        $this->adminB = Admin::create([
            'name' => 'Org B Admin',
            'email' => 'admin-b-forms@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationB->id,
        ]);
        $this->adminB->assignRole($role);

        Http::preventStrayRequests();
    }

    public function test_sync_is_rejected_when_tiktok_is_not_connected(): void
    {
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.sync-forms'))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'Connect a TikTok Ads account before syncing Lead Forms.');

        $this->assertDatabaseCount('tiktok_form_mappings', 0);
        Http::assertNothingSent();
    }

    public function test_sync_uses_only_the_current_organization_tiktok_connection(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'token-a');
        $this->createConnectedTikTok($this->organizationB, $this->adminB, '2222222222', 'token-b');

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $this->assertStringContainsString('/page/get/', $request->url());
            $this->assertSame('token-a', $request->header('Access-Token')[0] ?? null);
            $this->assertSame('1111111111', $request['advertiser_id']);
            $this->assertSame('LEAD_GEN', $request['business_type']);
            $this->assertNotSame('token-b', $request->header('Access-Token')[0] ?? null);
            $this->assertNotSame('2222222222', $request['advertiser_id']);

            return $this->pageGetResponse([[
                'page_id' => 'page-year-7',
                'title' => 'Year 7 Admissions',
                'status' => 'PUBLISHED',
            ]]);
        });

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.sync-forms'), [
                'advertiser_id' => '2222222222',
            ])
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tiktok_form_mappings', [
            'organization_id' => $this->organizationA->id,
            'advertiser_id' => '1111111111',
            'external_form_id' => 'page-year-7',
            'external_form_name' => 'Year 7 Admissions',
        ]);
        $this->assertDatabaseMissing('tiktok_form_mappings', [
            'organization_id' => $this->organizationB->id,
        ]);
    }

    public function test_tiktok_api_forms_normalize_correctly(): void
    {
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $this->assertStringContainsString('/page/get/', $request->url());
            $this->assertSame('LEAD_GEN', $request['business_type']);
            $page = (int) $request['page'];

            if ($page === 1) {
                return $this->pageGetResponse([
                    ['page_id' => 'page-1', 'title' => 'Year 7 Admissions', 'status' => 'PUBLISHED'],
                    ['page_id' => '', 'title' => 'Ignored', 'status' => 'EDITED'],
                ], page: 1, totalPage: 2);
            }

            return $this->pageGetResponse([
                ['page_id' => 'page-2', 'title' => 'Open Day', 'status' => 'EDITED'],
            ], page: 2, totalPage: 2);
        });

        $forms = app(TikTokApiClient::class)->listInstantForms('access-token', '1111111111');

        $this->assertSame([
            ['id' => 'page-1', 'name' => 'Year 7 Admissions', 'status' => 'PUBLISHED'],
            ['id' => 'page-2', 'name' => 'Open Day', 'status' => 'EDITED'],
        ], $forms);
    }

    public function test_tiktok_api_form_fields_normalize_correctly(): void
    {
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $this->assertStringContainsString('/lead/field/get/', $request->url());
            $this->assertSame('INSTANT_FORM', $request['lead_source']);
            $this->assertSame('page-1', $request['page_id']);
            $this->assertSame('1111111111', $request['advertiser_id']);

            return Http::response([
                'code' => 0,
                'message' => 'OK',
                'data' => [
                    'fields' => ['email', 'phone_number', 'name', 'Which year group?'],
                    'meta_data' => [
                        'page_id' => 'page-1',
                        'page_name' => 'Year 7',
                    ],
                ],
            ], 200);
        });

        $this->assertSame([
            ['id' => 'email', 'label' => 'Email'],
            ['id' => 'phone_number', 'label' => 'Phone Number'],
            ['id' => 'name', 'label' => 'Name'],
            ['id' => 'Which year group?', 'label' => 'Which year group?'],
        ], app(TikTokApiClient::class)->getInstantFormFields('access-token', '1111111111', 'page-1'));
    }

    public function test_sync_is_idempotent_and_does_not_delete_missing_forms(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $this->fakePageGet([[
            'page_id' => 'page-year-7',
            'title' => 'Year 7 Admissions',
            'status' => 'PUBLISHED',
        ]]);

        $this->actingAs($this->adminA, 'admin')->post(route('admin.integrations.tiktok.sync-forms'));
        $this->actingAs($this->adminA, 'admin')->post(route('admin.integrations.tiktok.sync-forms'));

        $this->assertSame(1, TikTokFormMapping::query()->count());

        $this->fakePageGet([]);
        $this->actingAs($this->adminA, 'admin')->post(route('admin.integrations.tiktok.sync-forms'));

        $this->assertDatabaseHas('tiktok_form_mappings', [
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'external_form_id' => 'page-year-7',
        ]);
    }

    public function test_resync_preserves_saved_admin_mapping(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $mapping = TikTokFormMapping::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '1111111111',
            'external_form_id' => 'page-year-7',
            'external_form_name' => 'Old Name',
            'external_status' => 'EDITED',
            'lead_source_label' => 'TikTok — Year 7',
            'assigned_to' => $this->adminA->id,
            'priority' => 'high',
            'auto_create_lead' => false,
            'is_active' => true,
            'field_mapping' => ['email' => 'email'],
            'external_fields' => ['email'],
        ]);

        $this->fakePageGet([[
            'page_id' => 'page-year-7',
            'title' => 'Year 7 Admissions',
            'status' => 'PUBLISHED',
        ]]);

        $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.sync-forms'))
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $mapping->refresh();
        $this->assertSame('Year 7 Admissions', $mapping->external_form_name);
        $this->assertSame('PUBLISHED', $mapping->external_status);
        $this->assertSame('TikTok — Year 7', $mapping->lead_source_label);
        $this->assertSame($this->adminA->id, $mapping->assigned_to);
        $this->assertSame('high', $mapping->priority->value);
        $this->assertFalse($mapping->auto_create_lead);
        $this->assertTrue($mapping->is_active);
        $this->assertSame(['email' => 'email'], $mapping->field_mapping);
    }

    public function test_another_organization_forms_cannot_be_viewed_or_updated(): void
    {
        $connectionB = $this->createConnectedTikTok($this->organizationB, $this->adminB, '2222222222', 'token-b');
        $mappingB = TikTokFormMapping::query()->create([
            'organization_id' => $this->organizationB->id,
            'integration_connection_id' => $connectionB->id,
            'advertiser_id' => '2222222222',
            'external_form_id' => 'page-b',
            'external_form_name' => 'Org B Secret Form',
            'lead_source_label' => 'TikTok — Secret',
            'priority' => 'medium',
            'is_active' => true,
            'field_mapping' => [],
            'external_fields' => ['email'],
        ]);

        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertDontSee('Org B Secret Form');

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.forms.configure', $mappingB))
            ->assertNotFound();

        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mappingB), $this->validMappingPayload())
            ->assertNotFound();

        $mappingB->refresh();
        $this->assertSame('TikTok — Secret', $mappingB->lead_source_label);
    }

    public function test_arbitrary_form_id_cannot_be_configured(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get('/admin/integrations/tiktok/forms/999999')
            ->assertNotFound();
    }

    public function test_arbitrary_advertiser_id_cannot_be_injected_into_a_form_mapping(): void
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $mapping = TikTokFormMapping::query()->create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $connection->id,
            'advertiser_id' => '9999999999',
            'external_form_id' => 'page-injected',
            'external_form_name' => 'Injected Advertiser Form',
            'lead_source_label' => 'Injected',
            'priority' => 'medium',
            'is_active' => true,
            'field_mapping' => [],
            'external_fields' => ['email'],
        ]);

        Http::fake();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->assertNotFound();

        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'advertiser_id' => '9999999999',
            ]))
            ->assertNotFound();
    }

    public function test_cross_organization_assignee_is_rejected(): void
    {
        $mapping = $this->syncedFormWithFields();

        $this->actingAs($this->adminA, 'admin')
            ->from(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'assigned_to' => $this->adminB->id,
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->assertSessionHasErrors('assigned_to');

        $mapping->refresh();
        $this->assertNull($mapping->assigned_to);
    }

    public function test_valid_current_organization_assignee_is_saved(): void
    {
        $mapping = $this->syncedFormWithFields();

        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'assigned_to' => $this->adminA->id,
                'lead_source_label' => 'TikTok — Year 7',
                'priority' => 'high',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('success');

        $mapping->refresh();
        $this->assertSame($this->adminA->id, $mapping->assigned_to);
        $this->assertSame('TikTok — Year 7', $mapping->lead_source_label);
        $this->assertSame('high', $mapping->priority->value);
    }

    public function test_active_boolean_persists_and_missing_checkbox_does_not_deactivate(): void
    {
        $mapping = $this->syncedFormWithFields();

        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'is_active' => '1',
                'auto_create_lead' => '1',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $this->assertTrue($mapping->fresh()->is_active);
        $this->assertTrue($mapping->fresh()->auto_create_lead);

        $payload = $this->validMappingPayload();
        unset($payload['is_active']);

        $this->actingAs($this->adminA, 'admin')
            ->from(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $payload)
            ->assertRedirect(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->assertSessionHasErrors('is_active');

        $this->assertTrue($mapping->fresh()->is_active);

        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'is_active' => '0',
                'auto_create_lead' => '0',
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $this->assertFalse($mapping->fresh()->is_active);
        $this->assertFalse($mapping->fresh()->auto_create_lead);
    }

    public function test_crm_field_mapping_only_accepts_allowed_fields_and_preserves_unmapped_tiktok_fields(): void
    {
        $mapping = $this->syncedFormWithFields();

        $this->actingAs($this->adminA, 'admin')
            ->from(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'field_mapping' => [
                    'email' => 'organization_id',
                ],
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->assertSessionHasErrors('field_mapping.email');

        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'field_mapping' => [
                    'email' => 'email',
                    'phone_number' => 'phone',
                    'name' => '',
                    'Which year group?' => '',
                    'injected_field' => 'first_name',
                ],
            ]))
            ->assertRedirect(route('admin.integrations.tiktok.show'));

        $mapping->refresh();
        $this->assertSame('email', $mapping->field_mapping['email']);
        $this->assertSame('phone', $mapping->field_mapping['phone_number']);
        $this->assertSame('', $mapping->field_mapping['name']);
        $this->assertSame('', $mapping->field_mapping['Which year group?']);
        $this->assertArrayNotHasKey('injected_field', $mapping->field_mapping);
    }

    public function test_configure_page_auto_maps_only_explicit_tiktok_fields(): void
    {
        $mapping = $this->syncedFormWithFields();

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->assertOk()
            ->assertSee('Year 7 Admissions')
            ->assertSee('Do not map')
            ->assertSee('TikTok — Year 7 Admissions')
            ->assertSee($this->adminA->name)
            ->assertDontSee($this->adminB->name);

        $this->assertNull($mapping->fresh()->field_mapping['name'] ?? null);
    }

    public function test_facebook_form_mappings_are_not_changed_by_tiktok_sync(): void
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

        $facebookMapping = IntegrationFormMapping::create([
            'organization_id' => $this->organizationA->id,
            'integration_connection_id' => $facebook->id,
            'external_form_id' => 'page-year-7',
            'external_form_name' => 'Facebook Year 7',
            'internal_label' => 'Facebook Year 7',
            'lead_source_label' => 'Facebook — Year 7',
            'assigned_to' => $this->adminA->id,
            'priority' => 'high',
            'auto_create_lead' => true,
            'is_active' => true,
            'field_mapping' => ['email' => 'email'],
        ]);

        $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->fakePageGet([[
            'page_id' => 'page-year-7',
            'title' => 'Year 7 Admissions',
            'status' => 'PUBLISHED',
        ]]);

        $this->actingAs($this->adminA, 'admin')->post(route('admin.integrations.tiktok.sync-forms'));

        $facebookMapping->refresh();
        $this->assertSame('Facebook Year 7', $facebookMapping->internal_label);
        $this->assertSame('Facebook — Year 7', $facebookMapping->lead_source_label);
        $this->assertSame('page-year-7', $facebookMapping->external_form_id);
        $this->assertSame(['email' => 'email'], $facebookMapping->field_mapping);
        $this->assertSame(1, IntegrationFormMapping::query()->count());
        $this->assertSame(1, TikTokFormMapping::query()->count());
    }

    public function test_setup_progress_stays_honest_through_sync_and_mapping(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA);

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Waiting for sync')
            ->assertSee('TikTok Lead Forms')
            ->assertSee('No TikTok Lead Forms found')
            ->assertSee('Setup required')
            ->assertSeeInOrder(['Pending', 'Map CRM Fields'])
            ->assertSeeInOrder(['In progress', 'Receive Leads Automatically'])
            ->assertDontSee('Subscribed');

        $this->fakePageGet([[
            'page_id' => 'page-year-7',
            'title' => 'Year 7 Admissions',
            'status' => 'PUBLISHED',
        ]]);

        $this->actingAs($this->adminA, 'admin')->post(route('admin.integrations.tiktok.sync-forms'));

        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('1 form(s)')
            ->assertSee('Year 7 Admissions')
            ->assertSee('Needs setup')
            ->assertSee('Setup required')
            ->assertSee('Receive Leads Automatically')
            ->assertDontSee('Subscribed');

        $mapping = TikTokFormMapping::query()->firstOrFail();
        $this->fakeLeadFields();
        $this->actingAs($this->adminA, 'admin')
            ->put(route('admin.integrations.tiktok.forms.update', $mapping), $this->validMappingPayload([
                'field_mapping' => [
                    'email' => 'email',
                    'phone_number' => '',
                    'name' => '',
                    'Which year group?' => '',
                ],
            ]));

        $response = $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.show'))
            ->assertOk()
            ->assertSee('Configured');

        $html = $response->getContent();
        $this->assertStringContainsString('Setup required', $html);
        $this->assertDoesNotMatchRegularExpression('/Complete[\s\S]{0,80}Receive Leads Automatically/', $html);
    }

    public function test_http_failures_during_sync_do_not_expose_secrets(): void
    {
        $this->createConnectedTikTok($this->organizationA, $this->adminA, '1111111111', 'tiktok-access-token');

        Http::fake(function () {
            throw new ConnectionException(
                'cURL error 28 for GET https://business-api.tiktok.com/open_api/v1.3/page/get/?advertiser_id=1111111111&access_token=tiktok-access-token&secret=tiktok-test-secret'
            );
        });

        $response = $this->actingAs($this->adminA, 'admin')
            ->post(route('admin.integrations.tiktok.sync-forms'));

        $response
            ->assertRedirect(route('admin.integrations.tiktok.show'))
            ->assertSessionHas('error', 'TikTok could not complete this request. Please try again.');

        $session = json_encode(session()->all()) ?: '';
        $this->assertStringNotContainsString('tiktok-access-token', $session);
        $this->assertStringNotContainsString('tiktok-test-secret', $session);
        $this->assertStringNotContainsString('tiktok-access-token', (string) $response->headers->get('Location'));
        $this->assertDatabaseCount('tiktok_form_mappings', 0);
    }

    /**
     * @param  array<int, array<string, mixed>>  $list
     */
    private function fakePageGet(array $list): void
    {
        Http::fake([
            '*page/get*' => $this->pageGetResponse($list),
        ]);
    }

    private function fakeLeadFields(): void
    {
        Http::fake([
            '*lead/field/get*' => Http::response([
                'code' => 0,
                'message' => 'OK',
                'data' => [
                    'fields' => ['email', 'phone_number', 'name', 'Which year group?'],
                    'meta_data' => [
                        'page_id' => 'page-year-7',
                        'page_name' => 'Year 7 Admissions',
                        'page_url' => '',
                        'create_time' => '2026-01-01 00:00:00',
                    ],
                ],
            ], 200),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $list
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     */
    private function pageGetResponse(array $list, int $page = 1, int $totalPage = 1)
    {
        return Http::response([
            'code' => 0,
            'message' => 'OK',
            'data' => [
                'list' => $list,
                'page_info' => [
                    'page' => $page,
                    'page_size' => 100,
                    'total_number' => count($list),
                    'total_page' => $totalPage,
                ],
            ],
        ], 200);
    }

    private function createConnectedTikTok(
        Organization $organization,
        Admin $admin,
        string $advertiserId = '1111111111',
        string $token = 'token-a',
    ): IntegrationConnection {
        return IntegrationConnection::create([
            'organization_id' => $organization->id,
            'platform' => IntegrationPlatform::TikTok,
            'status' => IntegrationConnectionStatus::Connected,
            'external_account_id' => $advertiserId,
            'external_account_name' => 'School Ads Account',
            'access_token' => $token,
            'connected_by' => $admin->id,
        ]);
    }

    private function syncedFormWithFields(): TikTokFormMapping
    {
        $connection = $this->createConnectedTikTok($this->organizationA, $this->adminA);
        $this->fakePageGet([[
            'page_id' => 'page-year-7',
            'title' => 'Year 7 Admissions',
            'status' => 'PUBLISHED',
        ]]);

        $this->actingAs($this->adminA, 'admin')->post(route('admin.integrations.tiktok.sync-forms'));

        $mapping = TikTokFormMapping::query()->firstOrFail();
        $this->fakeLeadFields();
        $this->actingAs($this->adminA, 'admin')
            ->get(route('admin.integrations.tiktok.forms.configure', $mapping))
            ->assertOk();

        return $mapping->fresh();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validMappingPayload(array $overrides = []): array
    {
        return array_merge([
            'lead_source_label' => 'TikTok — Year 7 Admissions',
            'assigned_to' => null,
            'priority' => 'medium',
            'auto_create_lead' => '1',
            'is_active' => '1',
            'field_mapping' => [
                'email' => 'email',
                'phone_number' => '',
                'name' => '',
                'Which year group?' => '',
            ],
        ], $overrides);
    }
}
