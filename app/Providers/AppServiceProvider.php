<?php

namespace App\Providers;

use App\Models\Crm\Customer;
use App\Models\Crm\Invoice;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadCategory;
use App\Models\Crm\LeadImport;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Models\Crm\SavedFilter;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\Template;
use App\Models\FormEntry;
use App\Models\Integrations\TikTokFormMapping;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Models\Setting;
use App\Policies\Crm\CustomerPolicy;
use App\Policies\Crm\FormEntryPolicy;
use App\Policies\Crm\InvoicePolicy;
use App\Policies\Crm\LeadPolicy;
use App\Policies\Crm\ProjectPolicy;
use App\Policies\Crm\QuotationPolicy;
use App\Policies\EmailMarketing\CampaignPolicy;
use App\Policies\EmailMarketing\MessagePolicy;
use App\Policies\EmailMarketing\TemplatePolicy;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;
use App\Services\EmailMarketing\FakeMailboxClient;
use App\Services\EmailMarketing\WebklexMailboxClient;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Lead::class => LeadPolicy::class,
        Customer::class => CustomerPolicy::class,
        Project::class => ProjectPolicy::class,
        Quotation::class => QuotationPolicy::class,
        Invoice::class => InvoicePolicy::class,
        FormEntry::class => FormEntryPolicy::class,
        Message::class => MessagePolicy::class,
        Campaign::class => CampaignPolicy::class,
        Template::class => TemplatePolicy::class,
    ];

    public function register(): void
    {
        $this->app->bind(MailboxClientInterface::class, function () {
            if (class_exists(\Webklex\PHPIMAP\ClientManager::class)) {
                return new WebklexMailboxClient;
            }

            return new FakeMailboxClient;
        });
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        $this->registerOrganizationScopedBindings();

        try {
            $settings = Setting::first();
        } catch (\Throwable) {
            $settings = null;
        }

        if ($settings) {
            config([
                'services.stripe.key' => $settings->stripe_key ?? env('STRIPE_KEY'),
                'services.stripe.secret' => $settings->stripe_secret ?? env('STRIPE_SECRET'),
            ]);
        } else {
            config([
                'services.stripe.key' => env('STRIPE_KEY'),
                'services.stripe.secret' => env('STRIPE_SECRET'),
            ]);
        }
    }

    private function registerOrganizationScopedBindings(): void
    {
        Route::bind('lead', fn (string $value) => Lead::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('leadImport', fn (string $value) => LeadImport::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('leadCategory', fn (string $value) => LeadCategory::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('savedFilter', fn (string $value) => SavedFilter::forCurrentOrganization()
            ->where('admin_id', auth('admin')->id())
            ->whereKey($value)
            ->firstOrFail());
        Route::bind('customer', fn (string $value) => Customer::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('project', fn (string $value) => Project::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('quotation', fn (string $value) => Quotation::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('invoice', fn (string $value) => Invoice::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('formEntry', fn (string $value) => FormEntry::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('form', fn (string $value) => \App\Models\Form::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('emMessage', fn (string $value) => Message::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('emCampaign', fn (string $value) => Campaign::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('emTemplate', fn (string $value) => Template::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('tiktokForm', fn (string $value) => TikTokFormMapping::forCurrentOrganization()->whereKey($value)->firstOrFail());
        Route::bind('tiktokSubmission', fn (string $value) => TikTokLeadSubmission::forCurrentOrganization()->whereKey($value)->firstOrFail());
    }
}
