<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\Organization;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantFormPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    public function test_tenant_preview_resolves_the_form_slug_not_the_school_slug(): void
    {
        $school = Organization::create(['name' => 'Preview school', 'slug' => 'preview-school', 'is_active' => true]);
        Form::create(['organization_id' => $school->id, 'name' => 'Application copy', 'slug' => 'application-copy', 'handler' => 'dynamic', 'is_active' => true]);

        PublicOrganizationContext::clear();
        $this->get('/w/preview-school/forms/application-copy')
            ->assertOk()->assertViewIs('frontend.dynamic-form')->assertViewHas('slug', 'application-copy');
    }

    public function test_tenant_preview_does_not_expose_another_schools_form(): void
    {
        $school = Organization::create(['name' => 'First school', 'slug' => 'first-school', 'is_active' => true]);
        Organization::create(['name' => 'Second school', 'slug' => 'second-school', 'is_active' => true]);
        Form::create(['organization_id' => $school->id, 'name' => 'Private application', 'slug' => 'private-application', 'handler' => 'dynamic', 'is_active' => true]);

        PublicOrganizationContext::clear();
        $this->get('/w/second-school/forms/private-application')->assertNotFound()->assertSee('Page Not Found');
    }

    public function test_inactive_forms_are_not_published_by_the_preview_fix(): void
    {
        $school = Organization::create(['name' => 'Preview school', 'slug' => 'preview-school', 'is_active' => true]);
        Form::create(['organization_id' => $school->id, 'name' => 'Draft form', 'slug' => 'draft-form', 'handler' => 'dynamic', 'is_active' => false]);

        PublicOrganizationContext::clear();
        $this->get('/w/preview-school/forms/draft-form')->assertNotFound();
    }
}
