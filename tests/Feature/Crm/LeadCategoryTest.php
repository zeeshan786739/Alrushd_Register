<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Models\Crm\LeadCategory;
use App\Models\Crm\LeadImport;
use App\Models\Crm\SavedFilter;
use App\Support\LeadCategorySchema;
use Illuminate\Http\UploadedFile;
use Tests\Support\LeadImportFixtureFactory;

class LeadCategoryTest extends CrmTestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue(LeadCategorySchema::ready(), 'Category migration must be present for these tests.');
        $this->tmpDir = sys_get_temp_dir().'/lead-category-tests-'.uniqid();
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

    public function test_category_is_organization_scoped(): void
    {
        $own = LeadCategory::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Student Recruitment',
            'is_active' => true,
        ]);
        $other = LeadCategory::create([
            'organization_id' => $this->organizationB->id,
            'name' => 'Student Recruitment',
            'is_active' => true,
        ]);

        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => new UploadedFile($path, 'web.xls', null, null, true),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.category.save', $import), [
                'lead_category_id' => $other->id,
            ])
            ->assertSessionHasErrors('lead_category_id');

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.category.save', $import), [
                'lead_category_id' => $own->id,
            ])
            ->assertRedirect(route('admin.crm.leads.import.map', $import));

        $this->assertSame($own->id, $import->fresh()->lead_category_id);
    }

    public function test_import_stores_category_and_confirmed_leads_inherit_it(): void
    {
        $category = LeadCategory::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Staff Recruitment',
            'icon' => 'solar:users-group-rounded-linear',
            'tone' => 'info',
            'is_active' => true,
        ]);

        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => new UploadedFile($path, 'web.xls', null, null, true),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();
        $this->assertSame(0, Lead::forOrganization($this->organizationA->id)->count());

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.category.save', $import), [
                'lead_category_id' => $category->id,
            ])
            ->assertRedirect();

        $this->assertSame(0, Lead::forOrganization($this->organizationA->id)->count());

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.map.save', $import), [
                'selected_sheet' => $import->selected_sheet,
                'header_row' => $import->header_row,
                'mapping' => $import->mapping,
                'options' => [
                    'duplicate_behavior' => 'skip',
                    'default_status' => 'new',
                    'default_priority' => 'medium',
                    'source_label' => 'Category import',
                ],
            ])
            ->assertRedirect(route('admin.crm.leads.import.preview', $import));

        $this->assertSame(0, Lead::forOrganization($this->organizationA->id)->where('source', 'file_import')->count());

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.import.preview', $import))
            ->assertOk()
            ->assertSee('Staff Recruitment', false);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.confirm', $import), ['confirm' => '1'])
            ->assertRedirect();

        $leads = Lead::forOrganization($this->organizationA->id)->where('source', 'file_import')->get();
        $this->assertGreaterThan(0, $leads->count());
        foreach ($leads as $lead) {
            $this->assertSame($category->id, $lead->lead_category_id);
            $this->assertSame('file_import', $lead->source);
        }

        $withCustom = $leads->first(fn (Lead $lead) => ! empty($lead->custom_data));
        $this->assertNotNull($withCustom);
    }

    public function test_segment_and_category_filter_and_export_respect_category(): void
    {
        $students = LeadCategory::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Students',
            'is_active' => true,
            'tone' => 'info',
        ]);
        $staff = LeadCategory::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Staff',
            'is_active' => true,
            'tone' => 'warning',
        ]);

        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'StudentLead',
            'lead_status' => 'new',
            'priority' => 'medium',
            'lead_category_id' => $students->id,
        ]);
        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'StaffLead',
            'lead_status' => 'contacted',
            'priority' => 'medium',
            'lead_category_id' => $staff->id,
        ]);
        Lead::create([
            'organization_id' => $this->organizationA->id,
            'source' => 'manual',
            'first_name' => 'NoCategory',
            'lead_status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertSee('Lead Segments', false)
            ->assertSee('Students', false)
            ->assertSee('Staff', false)
            ->assertSee('Uncategorized', false);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['lead_category_id' => $students->id]))
            ->assertOk()
            ->assertSee('StudentLead')
            ->assertDontSee('StaffLead')
            ->assertDontSee('NoCategory');

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['lead_category_id' => 'uncategorized']))
            ->assertOk()
            ->assertSee('NoCategory')
            ->assertDontSee('StudentLead');

        SavedFilter::create([
            'organization_id' => $this->organizationA->id,
            'admin_id' => $this->adminA->id,
            'module' => 'leads',
            'name' => 'Students only',
            'filters' => ['lead_category_id' => (string) $students->id],
        ]);

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertSee('Students only');

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.export', ['lead_category_id' => $students->id]))
            ->assertOk();
    }

    public function test_quick_create_category_from_import(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => new UploadedFile($path, 'web.xls', null, null, true),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.categories.store', $import), [
                'name' => 'Admissions',
                'icon' => 'solar:square-academic-cap-linear',
                'tone' => 'indigo',
            ])
            ->assertRedirect(route('admin.crm.leads.import.map', $import));

        $category = LeadCategory::forOrganization($this->organizationA->id)->where('name', 'Admissions')->first();
        $this->assertNotNull($category);
        $this->assertSame($category->id, $import->fresh()->lead_category_id);
        $this->assertSame('solar:square-academic-cap-linear', $category->icon);
        $this->assertSame('indigo', $category->tone);
    }

    public function test_category_can_be_created_with_name_only_and_defaults(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => new UploadedFile($path, 'web.xls', null, null, true),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.import.category', $import))
            ->assertOk()
            ->assertSee('Select Existing Category', false)
            ->assertSee('Create New Category', false)
            ->assertSee('Choose Icon', false)
            ->assertSee('Choose Color', false)
            ->assertDontSee('Iconify icon', false)
            ->assertSee('crm-icon-picker', false)
            ->assertSee('crm-color-picker', false)
            ->assertSee('No categories yet', false);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.categories.store', $import), [
                'name' => 'General Enquiry',
            ])
            ->assertRedirect(route('admin.crm.leads.import.map', $import));

        $category = LeadCategory::forOrganization($this->organizationA->id)->where('name', 'General Enquiry')->first();
        $this->assertNotNull($category);
        $this->assertSame(\App\Support\LeadCategoryUi::DEFAULT_ICON, $category->icon);
        $this->assertSame(\App\Support\LeadCategoryUi::DEFAULT_TONE, $category->tone);
        $this->assertSame($category->id, $import->fresh()->lead_category_id);
    }

    public function test_invalid_raw_icon_is_rejected(): void
    {
        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => new UploadedFile($path, 'web.xls', null, null, true),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.categories.store', $import), [
                'name' => 'Bad Icon',
                'icon' => 'not-a-real-icon',
                'tone' => 'info',
            ])
            ->assertSessionHasErrors('icon');
    }

    public function test_import_appends_to_existing_category_without_changing_old_leads(): void
    {
        $category = LeadCategory::create([
            'organization_id' => $this->organizationA->id,
            'name' => 'Student Recruitment',
            'icon' => 'solar:square-academic-cap-linear',
            'tone' => 'info',
            'is_active' => true,
        ]);

        $existingIds = [];
        for ($i = 0; $i < 3; $i++) {
            $lead = Lead::create([
                'organization_id' => $this->organizationA->id,
                'source' => 'manual',
                'first_name' => 'Existing'.$i,
                'email' => 'existing-'.$i.'@example.test',
                'lead_status' => 'new',
                'priority' => 'medium',
                'lead_category_id' => $category->id,
            ]);
            $existingIds[] = $lead->id;
        }

        $before = Lead::forOrganization($this->organizationA->id)
            ->whereIn('id', $existingIds)
            ->get(['id', 'source', 'lead_category_id', 'first_name', 'email', 'updated_at'])
            ->keyBy('id');

        $path = $this->tmpDir.'/web.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.store'), [
                'file' => new UploadedFile($path, 'web.xls', null, null, true),
            ])
            ->assertRedirect();

        $import = LeadImport::forOrganization($this->organizationA->id)->latest('id')->firstOrFail();

        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.import.category', $import))
            ->assertOk()
            ->assertSee('Student Recruitment', false)
            ->assertSee('3 leads', false)
            ->assertSee('data-crm-category-search', false);

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.category.save', $import), [
                'lead_category_id' => $category->id,
            ])
            ->assertRedirect(route('admin.crm.leads.import.map', $import));

        $this->assertSame(3, Lead::forOrganization($this->organizationA->id)->where('lead_category_id', $category->id)->count());

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.map.save', $import), [
                'selected_sheet' => $import->selected_sheet,
                'header_row' => $import->header_row,
                'mapping' => $import->mapping,
                'options' => [
                    'duplicate_behavior' => 'skip',
                    'default_status' => 'new',
                    'default_priority' => 'medium',
                    'source_label' => 'Append import',
                ],
            ])
            ->assertRedirect(route('admin.crm.leads.import.preview', $import));

        $this->assertSame(3, Lead::forOrganization($this->organizationA->id)->where('lead_category_id', $category->id)->count());

        $this->actingAsCrmAdmin()
            ->post(route('admin.crm.leads.import.confirm', $import), ['confirm' => '1'])
            ->assertRedirect();

        $imported = Lead::forOrganization($this->organizationA->id)
            ->where('source', 'file_import')
            ->where('lead_category_id', $category->id)
            ->get();
        $this->assertGreaterThan(0, $imported->count());

        $afterCount = Lead::forOrganization($this->organizationA->id)
            ->where('lead_category_id', $category->id)
            ->count();
        $this->assertSame(3 + $imported->count(), $afterCount);

        foreach ($existingIds as $id) {
            $fresh = Lead::forOrganization($this->organizationA->id)->findOrFail($id);
            $snapshot = $before[$id];
            $this->assertSame('manual', $fresh->source);
            $this->assertSame($category->id, (int) $fresh->lead_category_id);
            $this->assertSame($snapshot->first_name, $fresh->first_name);
            $this->assertSame($snapshot->email, $fresh->email);
        }
    }

    public function test_admin_layout_cache_busts_core_assets(): void
    {
        $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index'))
            ->assertOk()
            ->assertSee('admin/assets/css/style.css?v=', false)
            ->assertSee('admin/assets/css/alrushad-overrides.css?v=', false)
            ->assertSee('admin/assets/js/alrushad-ui.js?v=', false);
    }
}
