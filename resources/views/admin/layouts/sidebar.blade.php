{{--
  Admin sidebar — single source of truth for navigation IA.
  Permissions and route names are preserved; only grouping/labels/UX change.
--}}
@php
    use App\Support\AdminNav;
@endphp

<ul class="sidebar-menu" id="sidebar-menu" aria-label="Admin navigation">

    {{-- ================= DASHBOARD ================= --}}
    @canany(['view dashboard'])
    <li>
        <a href="{{ route('admin.dashboard') }}"
           class="{{ AdminNav::linkClass('admin.dashboard') }}"
           title="Dashboard">
            <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Dashboard</span>
        </a>
    </li>
    @endcanany

    {{-- ================= TEAM & ACCESS ================= --}}
    @canany([
        'create role','edit role','view role','delete role',
        'create permission','edit permission','view permission','delete permission',
        'create user','edit user','view user','delete user'
    ])
    <li class="sidebar-menu-group-title" role="presentation">People &amp; Access</li>
    <li>
        <a href="{{ route('admin.user-management.index') }}"
           class="{{ AdminNav::linkClass(['admin.user-management.*','admin.roles.*','admin.permissions.*','admin.users.*']) }}"
           title="Team &amp; Access">
            <iconify-icon icon="solar:users-group-two-rounded-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Team &amp; Access</span>
        </a>
    </li>
    @endcanany

    {{-- ================= CRM ================= --}}
    @if(plan_module('crm'))
    @canany(['view leads','view customers','view projects','view quotations','view invoices','manage crm documents'])
    <li class="sidebar-menu-group-title" role="presentation">CRM</li>
    <li class="{{ AdminNav::dropdownClass([
        'admin.crm.overview','admin.crm.leads.*','admin.crm.customers.*','admin.crm.projects.*',
        'admin.crm.quotations.*','admin.crm.invoices.*','admin.crm.settings.*'
    ]) }}">
        <a href="javascript:void(0)"
           role="button"
           aria-expanded="{{ AdminNav::expanded([
               'admin.crm.overview','admin.crm.leads.*','admin.crm.customers.*','admin.crm.projects.*',
               'admin.crm.quotations.*','admin.crm.invoices.*','admin.crm.settings.*'
           ]) }}"
           aria-controls="nav-crm"
           title="CRM">
            <iconify-icon icon="solar:chart-2-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>CRM</span>
        </a>
        <ul class="sidebar-submenu" id="nav-crm">
            <li>
                <a href="{{ route('admin.crm.overview') }}"
                   class="{{ AdminNav::linkClass('admin.crm.overview') }}"
                   title="Overview">
                    <iconify-icon icon="solar:widget-2-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Overview</span>
                </a>
            </li>
            @can('view leads')
            <li>
                <a href="{{ route('admin.crm.leads.index') }}"
                   class="{{ AdminNav::linkClass('admin.crm.leads.*') }}"
                   title="Leads">
                    <iconify-icon icon="solar:user-hand-up-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Leads</span>
                </a>
            </li>
            @endcan
            @can('view customers')
            <li>
                <a href="{{ route('admin.crm.customers.index') }}"
                   class="{{ AdminNav::linkClass('admin.crm.customers.*') }}"
                   title="Customers">
                    <iconify-icon icon="solar:users-group-rounded-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Customers</span>
                </a>
            </li>
            @endcan
            @can('view projects')
            <li>
                <a href="{{ route('admin.crm.projects.index') }}"
                   class="{{ AdminNav::linkClass('admin.crm.projects.*') }}"
                   title="Projects">
                    <iconify-icon icon="solar:folder-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Projects</span>
                </a>
            </li>
            @endcan
            @can('view quotations')
            <li>
                <a href="{{ route('admin.crm.quotations.index') }}"
                   class="{{ AdminNav::linkClass('admin.crm.quotations.*') }}"
                   title="Quotations">
                    <iconify-icon icon="solar:document-text-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Quotations</span>
                </a>
            </li>
            @endcan
            @can('view invoices')
            <li>
                <a href="{{ route('admin.crm.invoices.index') }}"
                   class="{{ AdminNav::linkClass('admin.crm.invoices.*') }}"
                   title="Invoices">
                    <iconify-icon icon="solar:bill-list-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Invoices</span>
                </a>
            </li>
            @endcan
            @can('manage crm documents')
            <li>
                <a href="{{ route('admin.crm.settings.documents.edit') }}"
                   class="{{ AdminNav::linkClass('admin.crm.settings.*') }}"
                   title="Document Settings">
                    <iconify-icon icon="solar:settings-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Document Settings</span>
                </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcanany
    @endif

    {{-- ================= INTEGRATIONS ================= --}}
    @if(plan_module('integrations'))
    @canany(['view integrations', 'manage integrations'])
    <li class="sidebar-menu-group-title" role="presentation">Integrations</li>
    <li class="{{ AdminNav::dropdownClass([
        'admin.integrations.*'
    ]) }}">
        <a href="javascript:void(0)"
           role="button"
           aria-expanded="{{ AdminNav::expanded(['admin.integrations.*']) }}"
           aria-controls="nav-integrations"
           title="Integrations">
            <iconify-icon icon="solar:plug-circle-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Integrations</span>
        </a>
        <ul class="sidebar-submenu" id="nav-integrations">
            @can('view integrations')
            <li>
                <a href="{{ route('admin.integrations.hub') }}"
                   class="{{ AdminNav::linkClass('admin.integrations.hub') }}"
                   title="Integration Hub">
                    <iconify-icon icon="solar:widget-5-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Integration Hub</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.integrations.facebook.show') }}"
                   class="{{ AdminNav::linkClass('admin.integrations.facebook.*') }}"
                   title="Facebook Lead Ads">
                    <iconify-icon icon="logos:facebook" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Facebook Lead Ads</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.integrations.tiktok.show') }}"
                   class="{{ AdminNav::linkClass('admin.integrations.tiktok.*') }}"
                   title="TikTok Lead Generation">
                    <iconify-icon icon="logos:tiktok-icon" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>TikTok</span>
                </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcanany
    @endif

    {{-- ================= FORMS & INTAKE ================= --}}
    @php
        $canFormsSection = (
            plan_module('form_center')
            || plan_module('admissions')
        ) && (
            auth('admin')->check()
            || auth('admin')->user()?->canany([
                'create nationality','edit nationality','view nationality','delete nationality',
                'create admission_date','edit admission_date','view admission_date','delete admission_date',
                'create gender','edit gender','view gender','delete gender',
                'create relation_ship','edit relation_ship','view relation_ship','delete relation_ship',
                'create country','edit country','view country','delete country',
                'create terms_condition','edit terms_condition','view terms_condition','delete terms_condition',
                'create school','edit school','view school','delete school',
                'create year','edit year','view year','delete year',
                'create language','edit language','view language','delete language',
                'create subject','edit subject','view subject','delete subject',
                'create package','edit package','view package','delete package',
                'create course','edit course','view course','delete course',
                'create admission_studetns','edit admission_studetns','view admission_studetns','delete admission_studetns',
                'view job','view apply','view enquire','view referral','view subscribe',
            ])
        );
    @endphp
    @if($canFormsSection)
    <li class="sidebar-menu-group-title" role="presentation">Forms &amp; Intake</li>

    {{-- Form Center — build & publish forms --}}
    @if(plan_module('form_center') && auth('admin')->check())
    <li>
        <a href="{{ route('admin.form-manager.index') }}"
           class="{{ AdminNav::linkClass('admin.form-manager.*') }}"
           title="Build and manage dynamic forms">
            <iconify-icon icon="solar:widget-2-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Form Center</span>
        </a>
    </li>
    @endif

    {{-- Student application responses --}}
    @if(plan_module('admissions'))
    @canany(['create admission_studetns','edit admission_studetns','view admission_studetns','delete admission_studetns'])
    <li>
        <a href="{{ route('admin.form-students.index') }}"
           class="{{ AdminNav::linkClass('admin.form-students.*') }}"
           title="Review student enrollment applications">
            <iconify-icon icon="solar:user-id-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Student Applications</span>
        </a>
    </li>
    @endcanany
    @endif

    {{-- Enrollment catalog — single hub for all setup lists --}}
    @if(plan_module('admissions'))
    @canany([
        'create nationality','edit nationality','view nationality','delete nationality',
        'create admission_date','edit admission_date','view admission_date','delete admission_date',
        'create gender','edit gender','view gender','delete gender',
        'create relation_ship','edit relation_ship','view relation_ship','delete relation_ship',
        'create country','edit country','view country','delete country',
        'create terms_condition','edit terms_condition','view terms_condition','delete terms_condition',
        'create school','edit school','view school','delete school',
        'create year','edit year','view year','delete year',
        'create language','edit language','view language','delete language',
        'create subject','edit subject','view subject','delete subject',
        'create package','edit package','view package','delete package',
        'create course','edit course','view course','delete course',
    ])
    <li>
        <a href="{{ route('admin.enrollment-setup.index') }}"
           class="{{ AdminNav::linkClass(['admin.enrollment-setup.*','admin.student-course.*','admin.terms.*']) }}"
           title="Manage courses, packages, fields, and enrollment options">
            <iconify-icon icon="solar:settings-minimalistic-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Enrollment Setup</span>
        </a>
    </li>
    @endcanany
    @endif

    {{-- Legacy / API-connected form feeds --}}
    @canany(['view job','view apply','view enquire','view referral','view subscribe'])
    <li class="{{ AdminNav::dropdownClass([
        'admin.job-applications','admin.job-applications.*',
        'admin.apply-now','admin.online-madrasah',
        'admin.enquire-now','admin.referral-applications',
        'admin.direct-debit','admin.subscribe-applications'
    ]) }}">
        <a href="javascript:void(0)"
           role="button"
           aria-expanded="{{ AdminNav::expanded([
               'admin.job-applications','admin.job-applications.*',
               'admin.apply-now','admin.online-madrasah',
               'admin.enquire-now','admin.referral-applications',
               'admin.direct-debit','admin.subscribe-applications'
           ]) }}"
           aria-controls="nav-api-intake"
           title="Submissions from external website forms">
            <iconify-icon icon="solar:link-round-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Website Intake</span>
        </a>
        <ul class="sidebar-submenu" id="nav-api-intake">
            @can('view job')
            <li>
                <a href="{{ route('admin.job-applications') }}" class="{{ AdminNav::linkClass(['admin.job-applications','admin.job-applications.*']) }}" title="Job applications">
                    <iconify-icon icon="solar:case-minimalistic-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Job Applications</span>
                </a>
            </li>
            @endcan
            @can('view apply')
            <li>
                <a href="{{ route('admin.apply-now') }}" class="{{ AdminNav::linkClass('admin.apply-now') }}" title="Apply now submissions">
                    <iconify-icon icon="solar:pen-new-square-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Apply Now</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.online-madrasah') }}" class="{{ AdminNav::linkClass('admin.online-madrasah') }}" title="Online madrasah sign-ups">
                    <iconify-icon icon="solar:book-bookmark-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Online Madrasah</span>
                </a>
            </li>
            @endcan
            @can('view enquire')
            <li>
                <a href="{{ route('admin.enquire-now') }}" class="{{ AdminNav::linkClass('admin.enquire-now') }}" title="Enquiry form submissions">
                    <iconify-icon icon="solar:chat-round-dots-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Enquiries</span>
                </a>
            </li>
            @endcan
            @can('view referral')
            <li>
                <a href="{{ route('admin.referral-applications') }}" class="{{ AdminNav::linkClass('admin.referral-applications') }}" title="Referral submissions">
                    <iconify-icon icon="solar:share-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Referrals</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.direct-debit') }}" class="{{ AdminNav::linkClass('admin.direct-debit') }}" title="Direct debit sign-ups">
                    <iconify-icon icon="solar:card-transfer-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Direct Debit</span>
                </a>
            </li>
            @endcan
            @can('view subscribe')
            <li>
                <a href="{{ route('admin.subscribe-applications') }}" class="{{ AdminNav::linkClass('admin.subscribe-applications') }}" title="Newsletter / subscribe sign-ups">
                    <iconify-icon icon="solar:letter-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Subscribe</span>
                </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcanany
    @endif

    {{-- ================= EVENTS ================= --}}
    @if(plan_module('open_events'))
    @canany([
        'create open_event','edit open_event','view open_event','delete open_event',
        'create event_item','edit event_item','view event_item','delete event_item',
        'create meet_speakers','edit meet_speakers','view meet_speakers','delete meet_speakers',
        'create open_event_form','edit open_event_form','view open_event_form','delete open_event_form'
    ])
    <li class="sidebar-menu-group-title" role="presentation">Events</li>
    <li class="{{ AdminNav::dropdownClass([
        'admin.open-events.*','admin.open-event-items.*','admin.meet-speakers.*','admin.open-event-form.*'
    ]) }}">
        <a href="javascript:void(0)"
           role="button"
           aria-expanded="{{ AdminNav::expanded([
               'admin.open-events.*','admin.open-event-items.*','admin.meet-speakers.*','admin.open-event-form.*'
           ]) }}"
           aria-controls="nav-events"
           title="Open Events">
            <iconify-icon icon="solar:calendar-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Open Events</span>
        </a>
        <ul class="sidebar-submenu" id="nav-events">
            @canany(['create open_event','edit open_event','view open_event','delete open_event'])
            <li>
                <a href="{{ route('admin.open-events.index') }}" class="{{ AdminNav::linkClass('admin.open-events.*') }}" title="Events">
                    <iconify-icon icon="solar:calendar-mark-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Events</span>
                </a>
            </li>
            @endcanany
            @canany(['create event_item','edit event_item','view event_item','delete event_item'])
            <li>
                <a href="{{ route('admin.open-event-items.index') }}" class="{{ AdminNav::linkClass('admin.open-event-items.*') }}" title="Event Items">
                    <iconify-icon icon="solar:checklist-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Event Items</span>
                </a>
            </li>
            @endcanany
            @canany(['create meet_speakers','edit meet_speakers','view meet_speakers','delete meet_speakers'])
            <li>
                <a href="{{ route('admin.meet-speakers.index') }}" class="{{ AdminNav::linkClass('admin.meet-speakers.*') }}" title="Speakers">
                    <iconify-icon icon="solar:microphone-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Speakers</span>
                </a>
            </li>
            @endcanany
            @canany(['create open_event_form','edit open_event_form','view open_event_form','delete open_event_form'])
            <li>
                <a href="{{ route('admin.open-event-form.index') }}" class="{{ AdminNav::linkClass('admin.open-event-form.*') }}" title="Event Submissions">
                    <iconify-icon icon="solar:clipboard-list-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Event Submissions</span>
                </a>
            </li>
            @endcanany
        </ul>
    </li>
    @endcanany
    @endif

    {{-- ================= MARKETING ================= --}}
    @if(plan_module('email_marketing'))
    @canany([
        'view inbox','view sent emails','manage drafts','star emails','compose emails','view campaigns','view templates','manage mailbox settings'
    ])
    <li class="sidebar-menu-group-title" role="presentation">Marketing</li>
    @php
        $emailMarketingRoutes = [
            'admin.email.dashboard','admin.email.inbox','admin.email.compose','admin.email.sent','admin.email.drafts',
            'admin.email.starred','admin.email.campaigns.*','admin.email.templates.*','admin.email.mailbox.*',
        ];
        $emUnread = 0;
        try {
            if (auth('admin')->user()?->organization_id && auth('admin')->user()->can('view inbox')) {
                $emUnread = \App\Models\EmailMarketing\Message::forCurrentOrganization()->inbox()->unread()->count();
            }
        } catch (\Throwable) {}
    @endphp
    <li class="email-marketing-nav {{ AdminNav::dropdownClass($emailMarketingRoutes) }}">
        <a href="javascript:void(0)"
           role="button"
           aria-expanded="{{ AdminNav::expanded($emailMarketingRoutes) }}"
           aria-controls="nav-email-marketing"
           title="Email Marketing">
            <iconify-icon icon="solar:letter-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Email Marketing</span>
            @if($emUnread > 0)
                <span class="sidebar-badge" aria-label="{{ $emUnread }} unread">{{ $emUnread }}</span>
            @endif
        </a>
        <ul class="sidebar-submenu" id="nav-email-marketing">
            <li>
                <a href="{{ route('admin.email.dashboard') }}" class="{{ AdminNav::linkClass('admin.email.dashboard') }}" title="Email Marketing overview">
                    <iconify-icon icon="solar:widget-2-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Overview</span>
                </a>
            </li>
            @can('view inbox')
            <li>
                <a href="{{ route('admin.email.inbox') }}" class="{{ AdminNav::linkClass(['admin.email.inbox','admin.email.compose','admin.email.sent','admin.email.drafts','admin.email.starred']) }}" title="Email inbox">
                    <iconify-icon icon="solar:inbox-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Inbox</span>
                    @if($emUnread > 0)<span class="sidebar-badge">{{ $emUnread }}</span>@endif
                </a>
            </li>
            @endcan
            @can('view campaigns')
            <li>
                <a href="{{ route('admin.email.campaigns.index') }}" class="{{ AdminNav::linkClass('admin.email.campaigns.*') }}" title="Email campaigns">
                    <iconify-icon icon="solar:letter-opened-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Campaigns</span>
                </a>
            </li>
            @endcan
            @can('view templates')
            <li>
                <a href="{{ route('admin.email.templates.index') }}" class="{{ AdminNav::linkClass('admin.email.templates.*') }}" title="Email templates">
                    <iconify-icon icon="solar:clipboard-list-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Templates</span>
                </a>
            </li>
            @endcan
            @can('manage mailbox settings')
            <li>
                <a href="{{ route('admin.email.mailbox.settings') }}" class="{{ AdminNav::linkClass('admin.email.mailbox.*') }}" title="Email settings">
                    <iconify-icon icon="solar:settings-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
                    <span>Settings</span>
                </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcanany
    @endif

    {{-- ================= CONTENT ================= --}}
    @if(plan_module('website_cms'))
    @can('view setting')
    <li class="sidebar-menu-group-title" role="presentation">Content</li>
    @php $orgPublicUrl = auth('admin')->user()?->organization?->publicWebsiteUrl(); @endphp
    <li>
        <a href="{{ route('admin.settings.index') }}"
           class="{{ AdminNav::linkClass('admin.settings.*') }}"
           title="Website CMS">
            <iconify-icon icon="solar:monitor-smartphone-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Website CMS</span>
        </a>
    </li>
    @if($orgPublicUrl)
    <li>
        <a href="{{ $orgPublicUrl }}" target="_blank" rel="noopener"
           title="Open your public website">
            <iconify-icon icon="solar:globus-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Open my website</span>
        </a>
    </li>
    @endif
    @endcan
    @endif

    {{-- ================= ACCOUNT ================= --}}
    <li class="sidebar-menu-group-title" role="presentation">Account</li>
    <li>
        <a href="{{ route('admin.account.index') }}"
           class="{{ AdminNav::linkClass('admin.account.*') }}"
           title="Account">
            <iconify-icon icon="solar:user-id-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Account</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.account.payments.edit') }}"
           class="{{ AdminNav::linkClass('admin.account.payments.*') }}"
           title="Customer Payments">
            <iconify-icon icon="solar:wallet-money-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Customer Payments</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.account.website.edit') }}"
           class="{{ AdminNav::linkClass('admin.account.website.*') }}"
           title="Website URL &amp; custom domain">
            <iconify-icon icon="solar:link-circle-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Website URL</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.billing.index') }}"
           class="{{ AdminNav::linkClass('admin.billing.*') }}"
           title="Billing & Subscription">
            <iconify-icon icon="solar:card-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Billing &amp; Subscription</span>
        </a>
    </li>

</ul>
