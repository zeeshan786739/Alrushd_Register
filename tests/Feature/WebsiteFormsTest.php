<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Form;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\FormBuilderService;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website CMS → Website forms: which Form Center form each built-in website form posts to,
 * and the live check of the chosen form's real field keys against the keys the website sends.
 */
class WebsiteFormsTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Forms school', 'slug' => 'forms-school', 'is_active' => true]);
        WebsiteCms::forOrganization($this->school->id)
            ->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.forms-school.test'])->save();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);

        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@forms-school.test',
            'password' => bcrypt('password'),
            'organization_id' => $this->school->id,
        ]);
        $this->admin->assignRole($role);
        PublicOrganizationContext::clear();
    }

    protected function tearDown(): void
    {
        PublicOrganizationContext::clear();
        parent::tearDown();
    }

    /** @param  list<array{0: string, 1?: bool}>  $fields  [key, required] */
    private function makeForm(string $slug, string $name, array $fields, bool $active = true, ?int $orgId = null): Form
    {
        return app(FormBuilderService::class)->syncForm([
            'name' => $name,
            'slug' => $slug,
            'handler' => 'dynamic',
            'is_active' => $active,
            'placements' => [],
            'steps' => [['title' => 'Step', 'fields' => array_map(fn ($f) => [
                'key' => $f[0], 'label' => ucfirst(str_replace('_', ' ', $f[0])), 'type' => 'text', 'required' => $f[1] ?? false,
            ], $fields)]],
        ], new Form(['organization_id' => $orgId ?? $this->school->id]));
    }

    private function contactForm(string $slug = 'website-contact', string $name = 'Website: Contact'): Form
    {
        return $this->makeForm($slug, $name, [['full_name', true], ['phone'], ['email', true], ['topic'], ['message', true], ['consent', true]]);
    }

    private function checkForms(array $forms): array
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.website-forms.status'), ['forms' => $forms])
            ->assertOk()
            ->json('forms');
    }

    public function test_a_form_with_every_field_the_website_sends_is_connected(): void
    {
        $this->contactForm('enquiries', 'Enquiries');

        $contact = $this->checkForms(['contact' => 'enquiries'])['contact'];

        $this->assertSame('connected', $contact['status']);
        $this->assertSame('enquiries', $contact['form']['slug']);
        $this->assertSame([], $contact['missing']);
        $this->assertFalse($contact['automatic']);
    }

    public function test_missing_fields_are_listed_by_label_with_the_recommended_form(): void
    {
        $this->contactForm();
        $this->makeForm('short-contact', 'Short contact', [['full_name'], ['email'], ['message']]);

        $contact = $this->checkForms(['contact' => 'short-contact'])['contact'];

        $this->assertSame('missing', $contact['status']);
        $this->assertSame(['Phone', 'Enquiry about', 'Consent'], $contact['missing']);
        $this->assertSame('website-contact', $contact['recommended']['slug']);
    }

    public function test_a_required_field_the_website_never_sends_rejects_every_submission(): void
    {
        $this->makeForm('strict-contact', 'Strict contact', [
            ['full_name'], ['phone'], ['email'], ['topic'], ['message'], ['consent'], ['date_of_birth', true],
        ]);

        $contact = $this->checkForms(['contact' => 'strict-contact'])['contact'];

        $this->assertSame('rejected', $contact['status']);
        $this->assertSame(['Date of birth'], $contact['rejecting']);
    }

    public function test_no_matching_active_form_is_not_connected(): void
    {
        $this->makeForm('old-contact', 'Old contact', [['email']], active: false);
        $other = Organization::create(['name' => 'Other', 'slug' => 'other-school', 'is_active' => true]);
        $this->makeForm('website-review', 'Website: Review', [['email']], orgId: $other->id);

        $status = $this->checkForms(['contact' => 'old-contact', 'review' => '', 'newsletter' => 'deleted-form']);

        $this->assertSame('not_connected', $status['contact']['status']);   // switched off
        $this->assertSame('not_connected', $status['review']['status']);    // another school's form doesn't count
        $this->assertSame('not_connected', $status['newsletter']['status']); // deleted
        $this->assertNull($status['contact']['recommended']);
    }

    public function test_an_empty_choice_uses_the_websites_standard_form(): void
    {
        $this->contactForm();

        $contact = $this->checkForms(['contact' => ''])['contact'];

        $this->assertSame('connected', $contact['status']);
        $this->assertTrue($contact['automatic']);
        $this->assertSame('website-contact', $contact['form']['slug']);
    }

    public function test_assignments_are_saved_exactly_as_before(): void
    {
        $forms = ['apply' => 'website-apply', 'contact' => 'enquiries', 'review' => '', 'newsletter' => 'website-newsletter'];

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['website' => ['forms' => $forms]]])
            ->assertOk();

        // As before, an empty choice arrives as null (Laravel's ConvertEmptyStringsToNull); the
        // website treats it like '' and posts to its standard "Website: …" form.
        $this->assertSame(array_merge($forms, ['review' => null]), WebsiteCms::forOrganization($this->school->id)->fresh()->draft['website']['forms']);
    }

    public function test_the_cms_page_starts_with_each_forms_status(): void
    {
        $this->contactForm();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('website-cms-forms.js', false)
            ->assertSee('formStatus: {"apply":{', false)
            ->assertSee('Website: Contact', false);
    }
}
