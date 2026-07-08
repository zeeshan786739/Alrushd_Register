<?php

return [
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
