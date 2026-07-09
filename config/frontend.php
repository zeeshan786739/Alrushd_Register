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

    /*
    |--------------------------------------------------------------------------
    | Routes that still use custom Blade / multi-step pages (not Form Center UI)
    |--------------------------------------------------------------------------
    */
    'spa_legacy_routes' => [
        'student-admission',
        'book-a-call',
        'open-event',
        'admin/login',
    ],

    'landing' => [
        'stats' => [
            ['value' => 5000, 'suffix' => '+', 'label' => 'Students Enrolled'],
            ['value' => 50, 'suffix' => '+', 'label' => 'Qualified Teachers'],
            ['value' => 20, 'suffix' => '+', 'label' => 'Courses Offered'],
            ['value' => 95, 'suffix' => '%', 'label' => 'Parent Satisfaction'],
        ],
        'programs' => [
            ['title' => 'Primary Education', 'desc' => 'Foundational learning with Islamic values for young learners.', 'icon' => 'fa-book-open'],
            ['title' => 'Secondary Education', 'desc' => 'Rigorous academics preparing students for future success.', 'icon' => 'fa-graduation-cap'],
            ['title' => 'Quran Classes', 'desc' => 'Structured Quranic studies with qualified teachers.', 'icon' => 'fa-book-quran'],
            ['title' => 'Hifz Program', 'desc' => 'Dedicated memorisation programme with personalised support.', 'icon' => 'fa-star'],
            ['title' => 'Online Learning', 'desc' => 'Flexible live classes accessible from anywhere in the world.', 'icon' => 'fa-laptop'],
            ['title' => 'Weekend Classes', 'desc' => 'Supplementary weekend sessions for busy families.', 'icon' => 'fa-calendar-week'],
        ],
        'testimonials' => [
            ['name' => 'Fatima Ahmed', 'role' => 'Parent', 'text' => 'Al Rushd has transformed my children\'s love for learning. The teachers are caring, qualified, and truly dedicated.', 'rating' => 5],
            ['name' => 'Omar Hassan', 'role' => 'Parent', 'text' => 'The online platform is seamless and the Islamic environment gives us complete peace of mind as parents.', 'rating' => 5],
            ['name' => 'Aisha Khan', 'role' => 'Parent', 'text' => 'Flexible scheduling and excellent curriculum — our family couldn\'t be happier with Al Rushd Online School.', 'rating' => 5],
        ],
        'faq' => [
            ['q' => 'How do I apply?', 'a' => 'Choose the relevant form from our Forms section, complete each step, and submit. Our admissions team will guide you through the process.'],
            ['q' => 'How long is approval?', 'a' => 'Most applications are reviewed within 3–5 working days. You will receive email confirmation once your application is processed.'],
            ['q' => 'What documents are required?', 'a' => 'Typically a birth certificate, previous school records, and identification documents. Specific requirements vary by programme.'],
            ['q' => 'Can I apply online?', 'a' => 'Yes. All our admission and application forms are fully online — apply from anywhere, anytime.'],
            ['q' => 'Is there any fee?', 'a' => 'Fees vary by programme and package. Full details are provided during the admission process after your initial application.'],
        ],
        'contact' => [
            'email' => 'info@alrushd.online',
            'phone' => '+44 20 3633 0757',
            'address' => 'Al Rushd Online School, United Kingdom',
            'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2483.0!2d-0.1276!3d51.5074!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNTHCsDMwJzI2LjYiTiAwwrAwNyczOS40Ilc!5e0!3m2!1sen!2suk!4v1',
        ],
    ],

];
