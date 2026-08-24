<?php

return [

    'defaults' => [
        'currency' => env('SAAS_DEFAULT_CURRENCY', 'USD'),
        'billing_interval' => 'month',
        'trial_days' => 14,
        'price' => 0,
        'is_active' => true,
        'is_featured' => false,
        'is_default' => true,
        'sort_order' => 0,
    ],

    /*
    | Product modules — toggled per plan. Unchecked = hidden & blocked in tenant admin.
    */
    'modules' => [
        'crm' => [
            'label' => 'CRM',
            'description' => 'Leads, customers, projects, quotes & invoices',
            'icon' => 'solar:chart-2-linear',
            'marketing' => 'Admissions CRM & lead pipeline',
            'group' => 'Core',
        ],
        'form_center' => [
            'label' => 'Form Center',
            'description' => 'Build, publish, and manage dynamic forms',
            'icon' => 'solar:document-add-linear',
            'marketing' => 'Drag & drop form builder',
            'group' => 'Core',
        ],
        'form_submissions' => [
            'label' => 'Form Submissions',
            'description' => 'Unified inbox for every form entry',
            'icon' => 'solar:inbox-linear',
            'marketing' => 'Form submissions inbox',
            'group' => 'Core',
        ],
        'admissions' => [
            'label' => 'Admissions Setup',
            'description' => 'Courses, packages, fees & admission catalog',
            'icon' => 'solar:square-academic-cap-linear',
            'marketing' => 'Student admissions & fee catalog',
            'group' => 'Admissions',
        ],
        'staff_applications' => [
            'label' => 'Staff Applications',
            'description' => 'Staff hiring forms and applicant review',
            'icon' => 'solar:user-id-linear',
            'marketing' => 'Staff application workflows',
            'group' => 'Admissions',
        ],
        'open_events' => [
            'label' => 'Open Events',
            'description' => 'Event pages and registration forms',
            'icon' => 'solar:calendar-linear',
            'marketing' => 'Open days & event registration',
            'group' => 'Admissions',
        ],
        'integrations' => [
            'label' => 'Integrations',
            'description' => 'Facebook, TikTok, and lead sync connectors',
            'icon' => 'solar:link-circle-linear',
            'marketing' => 'Facebook & TikTok Lead Ads sync',
            'group' => 'Marketing',
        ],
        'email_marketing' => [
            'label' => 'Email Marketing',
            'description' => 'Campaigns, templates, and shared inbox',
            'icon' => 'solar:letter-linear',
            'marketing' => 'Email marketing & campaigns',
            'group' => 'Marketing',
        ],
        'website_cms' => [
            'label' => 'Website CMS',
            'description' => 'Public school landing page & branding',
            'icon' => 'solar:monitor-smartphone-linear',
            'marketing' => 'Website CMS & landing pages',
            'group' => 'Content',
        ],
    ],

    /*
    | Map admin route name patterns to a module key for access control.
    | Routes not listed here are always allowed (dashboard, account, billing, team).
    */
    'route_modules' => [
        'admin.crm.form-entries.*' => 'form_submissions',
        'admin.crm.*' => 'crm',
        'admin.email.*' => 'email_marketing',
        'admin.integrations.*' => 'integrations',
        'admin.form-manager.*' => 'form_center',
        'admin.form-students.*' => 'form_submissions',
        'admin.form-student-*' => 'form_submissions',
        'admin.settings.*' => 'website_cms',
        'admin.website-cms.*' => 'website_cms',
        'admin.open-events.*' => 'open_events',
        'admin.open-event-*' => 'open_events',
        'admin.staff-*' => 'staff_applications',
        'admin.staff_*' => 'staff_applications',
        'admin.groups.*' => 'admissions',
        'admin.group-*' => 'admissions',
        'admin.packages.*' => 'admissions',
        'admin.plans.*' => 'admissions',
        'admin.course-*' => 'admissions',
        'admin.qualifications.*' => 'admissions',
        'admin.time-tables.*' => 'admissions',
        'admin.subjects.*' => 'admissions',
        'admin.student-*' => 'admissions',
        'admin.admission-*' => 'admissions',
        'admin.enquire-*' => 'admissions',
        'admin.referral*' => 'admissions',
        'admin.debit*' => 'admissions',
        'admin.coupons.*' => 'admissions',
        'admin.cupon*' => 'admissions',
        'admin.meet-*' => 'admissions',
        'admin.metting*' => 'admissions',
        'admin.schools.*' => 'admissions',
        'admin.nationalities.*' => 'admissions',
        'admin.genders.*' => 'admissions',
        'admin.languages.*' => 'admissions',
        'admin.payment-countries.*' => 'admissions',
        'admin.relationships.*' => 'admissions',
    ],

    'limit_definitions' => [
        'max_admins' => [
            'label' => 'Staff accounts',
            'placeholder' => 'Unlimited',
            'help' => 'Maximum admin users for this school.',
        ],
        'max_leads' => [
            'label' => 'CRM leads',
            'placeholder' => 'Unlimited',
            'help' => 'Active leads stored in the pipeline.',
        ],
        'max_forms' => [
            'label' => 'Form Center forms',
            'placeholder' => 'Unlimited',
            'help' => 'Dynamic forms the school can publish.',
        ],
        'max_email_campaigns' => [
            'label' => 'Email campaigns / month',
            'placeholder' => 'Unlimited',
            'help' => 'Marketing campaigns sent per billing month.',
        ],
        'max_storage_mb' => [
            'label' => 'File storage (MB)',
            'placeholder' => 'Unlimited',
            'help' => 'Uploads, CMS assets, and attachments.',
        ],
    ],

];
