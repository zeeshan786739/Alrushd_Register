<?php

return [
    /*
    | CRM-managed lists (Admission Form sidebar modules).
    | Resolved from database via FormOptionsResolver — keep in sync with admin CRUD modules.
    */
    'option_sources' => [
        '' => 'Static options (manual list)',
        'nationalities' => 'Nationalities (Admission Form)',
        'genders' => 'Genders (Admission Form)',
        'relationships' => 'Relationships (Admission Form)',
        'payment_countries' => 'Payment Countries (Admission Form)',
        'admission_dates' => 'Admission Dates (Admission Form)',
        'schools' => 'Schools (Admission Form)',
        'student_groups' => 'Student Groups (Admission Form)',
        'student_years' => 'Year Groups (Admission Form)',
        'student_packages' => 'Packages (Admission Form)',
        'student_languages' => 'Languages (Admission Form)',
        'student_subjects' => 'Subjects (Admission Form)',
        'countries' => 'Countries (all countries)',
        'debit_groups' => 'Debit Groups (Student Setup)',
        'ethnicity' => 'Ethnicity (static list)',
        'job_marital' => 'Job marital status (static)',
        'staff_marital' => 'Staff marital status (static)',
        'job_departments' => 'Job departments (static)',
        'employment_types' => 'Employment types (static)',
        'working_hours' => 'Working hours (static)',
        'hear_about_job' => 'How did you hear about job (static)',
        'job_positions' => 'Job positions (static)',
        'yes_no' => 'Yes / No (static)',
    ],

    'option_source_groups' => [
        [
            'label' => 'Admission Form',
            'icon' => 'solar:square-academic-cap-linear',
            'sources' => [
                'nationalities', 'genders', 'relationships', 'payment_countries',
                'admission_dates', 'schools', 'student_groups', 'student_years',
                'student_packages', 'student_languages', 'student_subjects',
            ],
        ],
        [
            'label' => 'Other lookups',
            'icon' => 'solar:global-linear',
            'sources' => ['countries', 'debit_groups'],
        ],
        [
            'label' => 'Static lists',
            'icon' => 'solar:list-linear',
            'sources' => [
                'ethnicity', 'job_marital', 'staff_marital', 'job_departments',
                'employment_types', 'working_hours', 'hear_about_job', 'job_positions', 'yes_no',
            ],
        ],
    ],

    'genders' => ['Male', 'Female', 'Others'],
    'job_marital' => ['Single', 'Married', 'UnMarried', 'Others'],
    'staff_marital' => ['Single', 'Married', 'Others'],
    'ethnicity' => [
        'Asian',
        'Black / African / Caribbean / Black British',
        'White',
        'Mixed / Multiple Ethnic Groups',
        'Arab',
        'Hispanic / Latino',
        'Native American / Alaska Native',
        'Pacific Islander',
        'Other',
    ],
    'job_departments' => ['Primary', 'Secondary', 'Sixth Form', 'Admin', 'HR', 'Finance'],
    'employment_types' => ['Full-Time', 'Part-Time', 'Temporary / Contract'],
    'working_hours' => ['Weekdays', 'Weekends', 'Evening Only', 'Flexible'],
    'hear_about_job' => ['Website', 'Referral', 'Social Media', 'Job Board', 'Other'],
    'yes_no' => ['Yes', 'No'],
    'job_positions' => [
        'Primary School Teacher', 'Secondary School Teacher', 'English Teacher', 'Mathematics Teacher',
        'Science Teacher (General)', 'Biology Teacher', 'Chemistry Teacher', 'Physics Teacher',
        'Computer Science / ICT Teacher', 'History Teacher', 'Geography Teacher', 'Business Studies Teacher',
        'Economics Teacher', 'Sociology Teacher', 'Psychology Teacher', 'Accounting Teacher',
        'Art & Design Teacher', 'Physical Education (PE) Teacher', 'Arabic Language Teacher',
        'Islamic Studies Teacher', "Qur'an & Tajweed Teacher", 'Hifdh Teacher', 'Special Educational Needs (SEN) Teacher',
        'Teaching Assistant', 'Exam Invigilator', 'Homework Reviewer / Marker', 'Principal',
        'Vice Principal / Deputy Head Teacher', 'School Administrator', 'Admissions Officer',
        'HR / Recruitment Officer', 'Finance Officer / Accountant', 'Other',
    ],
    'field_types' => [
        'text' => 'Text',
        'email' => 'Email',
        'tel' => 'Phone',
        'date' => 'Date',
        'textarea' => 'Long text',
        'select' => 'Dropdown',
        'radio' => 'Radio buttons',
        'checkbox' => 'Checkbox',
        'file' => 'File upload',
        'payment' => 'Payment (Stripe)',
        'section' => 'Section divider',
        'repeater' => 'Repeatable group',
    ],

    'placements' => [
        'landing' => [
            'label' => 'Landing page',
            'description' => 'Hero buttons below the main banner',
            'icon' => 'solar:home-smile-angle-linear',
        ],
        'header' => [
            'label' => 'Header',
            'description' => 'Top navigation bar links',
            'icon' => 'solar:widget-4-linear',
        ],
        'footer' => [
            'label' => 'Footer',
            'description' => 'Footer quick links section',
            'icon' => 'solar:layers-minimalistic-linear',
        ],
    ],
];
