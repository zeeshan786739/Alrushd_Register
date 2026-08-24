<?php

namespace Tests\Feature\Crm;

use App\Models\Admin;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadCategory;
use App\Models\Crm\LeadImport;
use App\Support\LeadCategorySchema;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\LeadImportFixtureFactory;

class LeadImportTest extends CrmTestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir().'/lead-import-tests-'.uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir.'/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->tmpDir);
        parent::tearDown();
    }

    public function test_import_requires_permission(): void
    {
        $role = Role::create(['name' => 'no-import', 'guard_name' => 'admin']);
        $role->givePermissionTo(Permission::findByName('view leads', 'admin'));
        $this->adminA->syncRoles([$role]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.import.create'))
            ->assertForbidden();
    }

    public function test_other_organization_cannot_view_import(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $import = $this->uploadAndMap($path, 'Web Leads.xml.xls');

        $this->actingAsCrmAdmin($this->adminB)
            ->get(route('admin.crm.leads.import.preview', $import))
            ->assertNotFound();
    }

    public function test_upload_rejects_non_spreadsheet(): void
    {
        $path = $this->tmpDir.'/notes.txt';
        file_put_contents($path, 'not a spreadsheet');

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => $this->upload($path, 'notes.txt'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_spreadsheetml_xls_extension_imports_web_leads(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $import = $this->uploadAndMap($path, 'Web Leads 18-06-26.xls');
        $import = $import->fresh();

        $this->assertSame('spreadsheetml', $import->detected_format);
        $this->assertSame(3, $import->total_rows);
        $this->assertSame(1, $import->failed_rows);

        $this->confirm($import);

        $lead = Lead::forOrganization($this->organizationA->id)->where('email', 'Ada.Web@example.test')->first();
        $this->assertNotNull($lead);
        $this->assertSame('file_import', $lead->source);
        $this->assertSame('𝐀𝐝𝐚', $lead->first_name);
        $this->assertStringContainsString('+92', (string) $lead->phone);
        $this->assertSame('pkr_25,000_–_75,000', $lead->custom_data['what_is_your_estimated_project_budget?'] ?? null);
        $this->assertSame('Contact', $lead->form_name);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Custom Lead Information', false)
            ->assertSee('𝐀𝐝𝐚', false);
    }

    public function test_duplicate_headers_are_preserved_by_position(): void
    {
        $path = $this->tmpDir.'/meta.xlsx';
        LeadImportFixtureFactory::metaLeadsXlsx($path);
        $import = $this->uploadAndMap($path, 'Meta Leads.xlsx');
        $labels = array_column($import->detected_headers, 'label');
        $this->assertSame(17, count($labels));
        $this->assertSame('Follow Up', $labels[13]);
        $this->assertSame('Follow up', $labels[16]);
        $this->assertNotSame($import->detected_headers[13]['key'], $import->detected_headers[16]['key']);
    }

    public function test_meta_xlsx_stores_attribution_not_webhook_source(): void
    {
        Admin::create([
            'name' => 'Foysol',
            'email' => 'foysol@example.test',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);

        $path = $this->tmpDir.'/meta.xlsx';
        LeadImportFixtureFactory::metaLeadsXlsx($path);
        $import = $this->uploadAndMap($path, 'Meta Leads.xlsx');
        $this->confirm($import);

        $lead = Lead::forOrganization($this->organizationA->id)->where('email', 'meta.one@example.test')->first();
        $this->assertNotNull($lead);
        $this->assertSame('file_import', $lead->source);
        $this->assertNotSame('facebook_lead_ads', $lead->source);
        $this->assertSame('facebook', $lead->advertising_platform);
        $this->assertSame('Admissions 2026', $lead->campaign_name);
        $this->assertSame('UK Parents', $lead->adset_name);
        $this->assertSame('Spring Creative', $lead->ad_name);
        $this->assertSame('محمد', $lead->first_name);
        $this->assertSame('Year 7', $lead->custom_data['Student Year Group'] ?? null);
        $this->assertNotNull($lead->source_submitted_at);
        $this->assertTrue($lead->notes()->where('note', 'Called once, interested.')->exists());
        $this->assertTrue($lead->activities()->where('activity_type', 'imported')->exists());
        $this->assertSame('Foysol', $lead->assignedAdmin?->name);
        $this->assertNotNull($lead->next_follow_up_date);

        $ig = Lead::forOrganization($this->organizationA->id)->where('email', 'ivy.green@example.test')->first();
        $this->assertSame('instagram', $ig?->advertising_platform);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('File Import')
            ->assertSee('Admissions 2026')
            ->assertDontSee('Facebook Lead Ads');
    }

    public function test_unresolved_agent_stays_unassigned_with_warning(): void
    {
        $path = $this->tmpDir.'/meta.xlsx';
        LeadImportFixtureFactory::metaLeadsXlsx($path, false);
        $import = $this->uploadAndMap($path, 'Meta Leads.xlsx');
        $this->confirm($import);
        $lead = Lead::forOrganization($this->organizationA->id)->where('email', 'meta.one@example.test')->first();
        $this->assertNull($lead?->assigned_to);
        $this->assertTrue(collect($import->fresh()->rows()->orderBy('row_number')->first()->warnings ?? [])->contains(
            fn ($warning) => str_contains((string) $warning, 'could not be matched')
        ));
    }

    public function test_csv_with_different_headers_auto_maps(): void
    {
        $path = $this->tmpDir.'/alt.csv';
        LeadImportFixtureFactory::genericCsv($path);
        $import = $this->uploadAndMap($path, 'other-client.csv');
        $this->assertSame('csv', $import->detected_format);
        $this->assertSame('full_name', $import->mapping['col_0'] ?? null);
        $this->assertSame('phone', $import->mapping['col_1'] ?? null);
        $this->assertSame('email', $import->mapping['col_2'] ?? null);
        $this->assertSame('campaign_name', $import->mapping['col_3'] ?? null);
        $this->assertSame('company', $import->mapping['col_4'] ?? null);
        $this->assertSame('city', $import->mapping['col_5'] ?? null);
        $this->confirm($import);
        $lead = Lead::forOrganization($this->organizationA->id)->where('email', 'sam.rivera@example.test')->first();
        $this->assertSame('Sam', $lead?->first_name);
        $this->assertSame('Brand Burst', $lead?->campaign_name);
    }

    public function test_manual_mapping_override_and_ignore(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $import = $this->uploadAndMap($path, 'web.xls', ['col_6' => 'ignore']);
        $this->confirm($import);
        $lead = Lead::forOrganization($this->organizationA->id)->where('email', 'Ada.Web@example.test')->first();
        $this->assertNull($lead?->company);
    }

    public function test_duplicate_email_in_file_is_skipped_by_default(): void
    {
        $path = $this->tmpDir.'/meta.xlsx';
        LeadImportFixtureFactory::metaLeadsXlsx($path);
        $import = $this->uploadAndMap($path, 'Meta Leads.xlsx');
        $this->assertGreaterThan(0, $import->duplicate_rows);
        $this->confirm($import);
        $this->assertSame(1, Lead::forOrganization($this->organizationA->id)->where('email', 'ivy.green@example.test')->count());
    }

    public function test_duplicate_phone_in_file(): void
    {
        $path = $this->tmpDir.'/phones.xlsx';
        LeadImportFixtureFactory::duplicatePhoneXlsx($path);
        $import = $this->uploadAndMap($path, 'phones.xlsx');
        $this->assertSame(1, $import->duplicate_rows);
        $this->confirm($import);
        $this->assertSame(1, Lead::forOrganization($this->organizationA->id)->count());
    }

    public function test_same_org_crm_duplicate_is_detected_other_org_is_not(): void
    {
        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'Existing',
            'email' => 'Ada.Web@example.test',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);
        Lead::create([
            'organization_id' => $this->organizationB->id,
            'source' => 'manual',
            'first_name' => 'Other Org',
            'email' => 'uk.lead@example.test',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $import = $this->uploadAndMap($path, 'web.xls');
        $this->confirm($import);

        $this->assertSame(1, Lead::forOrganization($this->organizationA->id)->where('email', 'Ada.Web@example.test')->count());
        $this->assertNotNull(Lead::forOrganization($this->organizationA->id)->where('email', 'uk.lead@example.test')->first());
        $this->assertSame('file_import', Lead::forOrganization($this->organizationA->id)->where('email', 'uk.lead@example.test')->value('source'));
    }

    public function test_create_anyway_imports_duplicates(): void
    {
        $path = $this->tmpDir.'/meta.xlsx';
        LeadImportFixtureFactory::metaLeadsXlsx($path);
        $import = $this->uploadAndMap($path, 'Meta Leads.xlsx', [], ['duplicate_behavior' => 'create']);
        $this->confirm($import);
        $this->assertSame(2, Lead::forOrganization($this->organizationA->id)->where('email', 'ivy.green@example.test')->count());
    }

    public function test_invalid_row_does_not_block_valid_rows(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $import = $this->uploadAndMap($path, 'web.xls');
        $this->confirm($import);
        $this->assertSame(2, Lead::forOrganization($this->organizationA->id)->where('source', 'file_import')->count());
        $this->assertGreaterThan(0, $import->fresh()->failed_rows);
    }

    public function test_repeating_the_same_file_is_detected(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $first = $this->uploadAndMap($path, 'web.xls');
        $this->confirm($first);

        $second = $this->uploadAndMap($path, 'web.xls');
        $this->assertGreaterThan(0, $second->duplicate_rows);
        $this->confirm($second);
        $this->assertSame(2, Lead::forOrganization($this->organizationA->id)->where('source', 'file_import')->count());
    }

    public function test_import_history_totals_and_index_action(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $import = $this->uploadAndMap($path, 'web.xls');
        $this->confirm($import);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.import.index'))
            ->assertOk()
            ->assertSee('web.xls');

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertSee('Import Leads');
    }

    public function test_numeric_phone_is_not_scientific_notation(): void
    {
        $path = $this->tmpDir.'/meta.xlsx';
        LeadImportFixtureFactory::metaLeadsXlsx($path);
        $import = $this->uploadAndMap($path, 'Meta Leads.xlsx');
        $this->confirm($import);
        $lead = Lead::forOrganization($this->organizationA->id)->where('email', 'meta.one@example.test')->first();
        $this->assertDoesNotMatchRegularExpression('/e\+/i', (string) $lead?->phone);
        $this->assertStringContainsString('447700900999', preg_replace('/\D+/', '', (string) $lead?->phone) ?? '');
    }

    /**
     * @param  array<string, string>  $mappingOverrides
     * @param  array<string, mixed>  $options
     */
    private function uploadAndMap(string $path, string $name, array $mappingOverrides = [], array $options = []): LeadImport
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => $this->upload($path, $name),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();

        if (LeadCategorySchema::ready()) {
            $category = LeadCategory::query()->firstOrCreate(
                [
                    'organization_id' => $this->organizationA->id,
                    'name' => 'General Enquiry',
                ],
                [
                    'icon' => 'solar:folder-with-files-linear',
                    'tone' => 'neutral',
                    'is_active' => true,
                ]
            );

            $this->actingAsCrmAdmin()
                ->post(route('admin.crm.leads.import.category.save', $import), [
                    'lead_category_id' => $category->id,
                ])
                ->assertRedirect(route('admin.crm.leads.import.map', $import));

            $import = $import->fresh();
        }

        $mapping = array_merge($import->mapping ?? [], $mappingOverrides);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.map.save', $import), [
                'selected_sheet' => $import->selected_sheet,
                'header_row' => $import->header_row,
                'mapping' => $mapping,
                'options' => array_merge([
                    'duplicate_behavior' => 'skip',
                    'default_status' => 'new',
                    'default_priority' => 'medium',
                    'source_label' => 'Test import',
                ], $options),
            ])
            ->assertRedirect(route('admin.crm.leads.import.preview', $import));

        return $import->fresh();
    }

    private function confirm(LeadImport $import): void
    {
        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.confirm', $import), ['confirm' => '1'])
            ->assertRedirect();
    }

    private function upload(string $path, string $name): UploadedFile
    {
        return new UploadedFile($path, $name, null, null, true);
    }
}
