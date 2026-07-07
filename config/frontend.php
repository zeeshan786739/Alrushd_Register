<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hero banner action buttons (matches legacy homepage)
    |--------------------------------------------------------------------------
    */
    'hero_buttons' => [
        [
            'label' => 'Job Application',
            'href' => '/job-applications',
            'variant' => 'gold',
        ],
        [
            'label' => 'Staff Admission',
            'href' => '/staff-application',
            'variant' => 'gold',
        ],
        [
            'label' => 'Student Admission',
            'href' => '/student-admission/step/1',
            'variant' => 'outline',
        ],
        [
            'label' => 'Contact Us',
            'href' => '/book-a-call',
            'variant' => 'gold',
        ],
        [
            'label' => 'Debit Form',
            'href' => '/debit-form',
            'variant' => 'outline',
        ],
        [
            'label' => 'Profile',
            'href' => '/admin/login',
            'variant' => 'outline',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary CTA destinations
    |--------------------------------------------------------------------------
    */
    'ctas' => [
        'book_a_call' => '/book-a-call',
        'student_admission' => '/student-admission/step/1',
        'enquire_now' => '/enquire-now',
        'referral' => '/referral',
        'open_event' => '/open-event',
        'staff_application' => '/staff-application',
        'job_applications' => '/job-applications',
        'debit_form' => '/debit-form',
        'prospectus' => '/enquire-now',
    ],

    'footer_explore' => [
        ['label' => 'Why Al-Rushd', 'href' => '#philosophy'],
        ['label' => 'Curriculum', 'href' => '#academics'],
        ['label' => 'Islamic Ethos', 'href' => '#features'],
        ['label' => 'Reviews', 'href' => '#testimonials'],
        ['label' => 'Admissions', 'href' => '#admissions'],
        ['label' => 'FAQs', 'href' => '#faq'],
    ],

    'form_endpoints' => [
        'enquire' => '/enquire/store',
        'referral' => '/referral/store',
        'meeting' => '/meeting/store',
        'event' => '/event-store',
        'debit' => '/debit/store',
        'staff' => '/staff-applications-form',
        'job' => '/job-applications-form',
        'pay_now' => '/pay-now',
        'admission_step' => '/student-admission/step',
    ],

];
