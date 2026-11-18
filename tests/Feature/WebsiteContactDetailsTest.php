<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use App\Support\ContactDetails;
use App\Support\PublicOrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website CMS → Contact details: the opening-hours text and WhatsApp link the website reads
 * are generated from structured settings; older values stay exactly as they were.
 */
class WebsiteContactDetailsTest extends TestCase
{
    use RefreshDatabase;

    private const MON_FRI = [true, true, true, true, true, false, false];

    private Organization $school;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true])]);

        $this->school = Organization::create(['name' => 'Contact school', 'slug' => 'contact-school', 'is_active' => true]);
        $cms = WebsiteCms::forOrganization($this->school->id);
        $cms->fill(['mode' => WebsiteCms::MODE_HEADLESS, 'external_url' => 'https://www.contact-school.test'])->save();
        $this->token = $cms->rotateApiToken();

        foreach (['view setting', 'edit setting'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        $role = Role::findOrCreate('website-editor', 'admin');
        $role->givePermissionTo(['view setting', 'edit setting']);
        $this->admin = Admin::create([
            'name' => 'Editor',
            'email' => 'editor@contact-school.test',
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

    private function saveContact(array $contact)
    {
        return $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.website-cms.draft'), ['data' => ['contact' => $contact]]);
    }

    private function draftContact(): array
    {
        return WebsiteCms::forOrganization($this->school->id)->fresh()->draft['contact'];
    }

    /* ── hours text ── */

    public function test_consecutive_days_become_ranges(): void
    {
        $this->assertSame('Mon–Fri, 8:30am–4:30pm (UK)', ContactDetails::hoursText(self::MON_FRI, '08:30', '16:30', 'UK'));
        $this->assertSame('Mon–Sun, 9am–5pm (UAE)', ContactDetails::hoursText(array_fill(0, 7, true), '09:00', '17:00', 'UAE'));
        $this->assertSame('Mon–Wed, Fri, 10am–12pm (Pakistan)', ContactDetails::hoursText([true, true, true, false, true, false, false], '10:00', '12:00', 'Pakistan'));
    }

    public function test_two_days_and_single_days_are_listed(): void
    {
        $this->assertSame('Sat, Sun, 9am–1pm (UK)', ContactDetails::hoursText([false, false, false, false, false, true, true], '09:00', '13:00', 'UK'));
        $this->assertSame('Mon, Wed, 8:15am–12:45pm (UK)', ContactDetails::hoursText([true, false, true, false, false, false, false], '08:15', '12:45', 'UK'));
        $this->assertSame('Fri, 12am–11:59pm (UK)', ContactDetails::hoursText([false, false, false, false, true, false, false], '00:00', '23:59', 'UK'));
    }

    public function test_no_days_means_closed(): void
    {
        $this->assertSame('Closed', ContactDetails::hoursText(array_fill(0, 7, false), '08:30', '16:30', 'UK'));
    }

    public function test_hours_text_is_read_back_only_when_it_would_be_written_the_same(): void
    {
        $this->assertSame(
            ['opening_days' => self::MON_FRI, 'opening_from' => '08:30', 'opening_until' => '16:30', 'opening_timezone' => 'UK'],
            ContactDetails::parseHours('Mon–Fri, 8:30am–4:30pm (UK)'),
        );
        $this->assertSame([false, false, false, false, false, true, true], ContactDetails::parseHours('Sat, Sun, 9am–1pm (UK)')['opening_days']);

        // Worded differently: kept as it is rather than reworded on the website.
        $this->assertNull(ContactDetails::parseHours('Monday – Friday, 8:30 am – 6:00 pm'));
        $this->assertNull(ContactDetails::parseHours('Mon–Fri, 8:30am–4:30pm'));
        $this->assertNull(ContactDetails::parseHours('Mon–Fri, 8:30am–4:30pm (Mars)'));
    }

    /* ── WhatsApp link ── */

    public function test_whatsapp_link_uses_digits_only_and_an_encoded_message(): void
    {
        $this->assertSame('https://wa.me/442036330757', ContactDetails::whatsappLink('+44 20 3633 0757'));
        $this->assertSame(
            'https://wa.me/442036330757?text=Hello%2C%20I%27d%20like%20to%20ask%20about%20admissions',
            ContactDetails::whatsappLink('+44 (20) 3633-0757', "Hello, I'd like to ask about admissions"),
        );
        $this->assertSame('', ContactDetails::whatsappLink(''));
    }

    public function test_whatsapp_link_is_read_back_into_number_and_message(): void
    {
        $this->assertSame(
            ['whatsapp_same_as_phone' => true, 'whatsapp_number' => '', 'whatsapp_message' => ''],
            ContactDetails::parseWhatsapp('https://wa.me/442036330757', '+44 20 3633 0757'),
        );
        $this->assertSame(
            ['whatsapp_same_as_phone' => false, 'whatsapp_number' => '+447700900123', 'whatsapp_message' => 'Hi there'],
            ContactDetails::parseWhatsapp('https://wa.me/447700900123?text=Hi%20there', '+44 20 3633 0757'),
        );
        $this->assertNull(ContactDetails::parseWhatsapp('https://whatsapp.com/channel/0029Va', '+44 20 3633 0757'));
    }

    /* ── saving ── */

    public function test_saving_generates_the_text_and_link_the_website_reads(): void
    {
        $this->saveContact([
            'phone' => '+44 20 3633 0757',
            'email' => 'admin@contact-school.test',
            'whatsapp_enabled' => true,
            'whatsapp_same_as_phone' => true,
            'whatsapp_message' => 'Hello',
            'opening_days' => [true, true, true, true, false, false, false],
            'opening_from' => '09:00',
            'opening_until' => '15:30',
            'opening_timezone' => 'Pakistan',
        ])->assertOk();

        $c = $this->draftContact();
        $this->assertSame('Mon–Thu, 9am–3:30pm (Pakistan)', $c['working_hours']);
        $this->assertSame('https://wa.me/442036330757?text=Hello', $c['whatsapp']);

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.website-cms.publish'))->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$this->token)->getJson('/api/headless/v1/site')
            ->assertOk()
            ->assertJsonPath('settings.contact.working_hours', 'Mon–Thu, 9am–3:30pm (Pakistan)')
            ->assertJsonPath('settings.contact.whatsapp', 'https://wa.me/442036330757?text=Hello')
            ->assertJsonPath('settings.contact.whatsapp_enabled', true);
    }

    public function test_switching_whatsapp_off_keeps_the_number_but_tells_the_website(): void
    {
        $this->saveContact([
            'phone' => '+44 20 3633 0757', 'whatsapp_enabled' => false,
            'whatsapp_same_as_phone' => false, 'whatsapp_number' => '+44 7700 900123',
        ])->assertOk();

        $c = $this->draftContact();
        $this->assertFalse($c['whatsapp_enabled']);
        $this->assertSame('https://wa.me/447700900123', $c['whatsapp']);
    }

    public function test_older_values_are_kept_exactly_until_edited(): void
    {
        $merged = app(WebsiteCmsService::class)->merge(['contact' => [
            'phone' => '+44 20 3633 0757',
            'whatsapp' => 'https://whatsapp.com/channel/0029Va',
            'working_hours' => 'Monday – Friday, 8:30 am – 6:00 pm',
        ]])['contact'];

        $this->assertSame('https://whatsapp.com/channel/0029Va', $merged['whatsapp']);
        $this->assertSame('Monday – Friday, 8:30 am – 6:00 pm', $merged['working_hours']);
        $this->assertTrue($merged['whatsapp_enabled']);
        $this->assertNull($merged['opening_days']);

        // No WhatsApp link before: nothing is generated, so nothing new appears on the website.
        $none = app(WebsiteCmsService::class)->merge(['contact' => ['phone' => '+44 20 3633 0757', 'whatsapp' => null]])['contact'];
        $this->assertSame('', $none['whatsapp']);
        $this->assertFalse($none['whatsapp_enabled']);
    }

    /* ── validation ── */

    public function test_phone_email_whatsapp_and_hours_are_validated(): void
    {
        $this->saveContact(['phone' => '12345'])->assertStatus(422)->assertJsonValidationErrors(['contact.phone']);
        $this->saveContact(['email' => 'admin@'])->assertStatus(422)->assertJsonValidationErrors(['contact.email']);
        $this->saveContact(['whatsapp_enabled' => true, 'whatsapp_same_as_phone' => false, 'whatsapp_number' => ''])
            ->assertStatus(422)->assertJsonValidationErrors(['contact.whatsapp_number']);
        $this->saveContact(['opening_days' => self::MON_FRI, 'opening_from' => '16:30', 'opening_until' => '08:30', 'opening_timezone' => 'UK'])
            ->assertStatus(422)->assertJsonValidationErrors(['contact.opening_until']);

        // Closed every day: the times don't matter.
        $this->saveContact(['opening_days' => array_fill(0, 7, false), 'opening_from' => '16:30', 'opening_until' => '08:30', 'opening_timezone' => 'UK'])->assertOk();
        $this->assertSame('Closed', $this->draftContact()['working_hours']);
    }

    public function test_migration_reads_existing_values_without_changing_them(): void
    {
        DB::table('website_cms')->where('organization_id', $this->school->id)->update([
            'draft' => json_encode(['contact' => ['phone' => '+44 20 3633 0757', 'whatsapp' => 'https://wa.me/442036330757', 'working_hours' => 'Mon–Fri, 8:30am–4:30pm (UK)']]),
            'published' => json_encode(['contact' => ['phone' => '+44 20 3633 0757', 'whatsapp' => null, 'working_hours' => 'Weekdays 9 till 5']]),
        ]);

        (require database_path('migrations/2026_11_17_110000_add_structured_contact_details_to_website_cms.php'))->up();

        $row = DB::table('website_cms')->where('organization_id', $this->school->id)->first();
        $draft = json_decode($row->draft, true)['contact'];
        $published = json_decode($row->published, true)['contact'];

        $this->assertTrue($draft['whatsapp_enabled']);
        $this->assertTrue($draft['whatsapp_same_as_phone']);
        $this->assertSame(self::MON_FRI, $draft['opening_days']);
        $this->assertSame('https://wa.me/442036330757', $draft['whatsapp']);
        $this->assertSame('Mon–Fri, 8:30am–4:30pm (UK)', $draft['working_hours']);

        $this->assertFalse($published['whatsapp_enabled']);
        $this->assertArrayNotHasKey('opening_days', $published);
        $this->assertSame('Weekdays 9 till 5', $published['working_hours']);
    }
}
