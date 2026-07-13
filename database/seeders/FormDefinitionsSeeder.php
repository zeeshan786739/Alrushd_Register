<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Services\FormBuilderService;
use Illuminate\Database\Seeder;

class FormDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        $builder = app(FormBuilderService::class);

        foreach ($this->definitions() as $definition) {
            $existing = Form::where('slug', $definition['slug'])->first();
            $builder->syncForm($definition, $existing);
        }
    }

    private function field(string $key, string $label, string $type = 'text', array $extra = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'required' => false,
            'col_span' => 1,
        ], $extra);
    }

    private function definitions(): array
    {
        return [
            $this->enquireForm(),
            $this->referralForm(),
            $this->debitForm(),
            $this->jobForm(),
            $this->staffForm(),
            $this->meetingForm(),
            $this->studentAdmissionForm(),
        ];
    }

    private function enquireForm(): array
    {
        return [
            'name' => 'Enquire/Apply Now',
            'slug' => 'enquire-now',
            'legacy_route' => '/enquire-now',
            'legacy_table' => 'enquires',
            'success_route' => '/enquire-success',
            'submit_method' => 'urlencoded',
            'show_on_landing' => true,
            'hero_label' => 'Enquire Now',
            'hero_variant' => 'outline',
            'sort_order' => 10,
            'settings' => ['heading' => 'Enquire/Apply Now'],
            'steps' => [
                [
                    'title' => 'Enquire / Apply Now',
                    'description' => 'Parent and student details',
                    'fields' => [
                        $this->field('_section_parent', 'Parent/Guardian Contact Information', 'section', ['col_span' => 2]),
                        $this->field('fname', 'Name', 'text', ['required' => true, 'settings' => ['help_text' => 'First']]),
                        $this->field('phone', 'Phone', 'tel', ['required' => true, 'col_span' => 2]),
                        $this->field('email', 'Email', 'email', ['required' => true]),
                        $this->field('confirm_email', 'Confirm Email', 'email', ['required' => true]),
                        $this->field('_section_student', 'STUDENT DETAILS', 'section', ['col_span' => 2]),
                        $this->field('student_name', 'Full Name', 'text', ['required' => true]),
                        $this->field('year_group', 'Year Group', 'select', ['required' => true, 'options_source' => 'student_years']),
                        $this->field('gender', 'Gender', 'radio', ['required' => true, 'options_source' => 'genders', 'col_span' => 2]),
                        $this->field('nationality', 'Nationality', 'select', ['required' => true, 'options_source' => 'nationalities']),
                        $this->field('add_another_student', 'Do you want to add another Student?', 'radio', [
                            'options_source' => 'yes_no',
                            'col_span' => 2,
                        ]),
                    ],
                ],
            ],
        ];
    }

    private function referralForm(): array
    {
        return [
            'name' => 'Referral',
            'slug' => 'referral',
            'legacy_route' => '/referral',
            'legacy_table' => 'referrals',
            'success_route' => '/referral-success',
            'submit_method' => 'urlencoded',
            'show_on_landing' => false,
            'sort_order' => 11,
            'settings' => ['heading' => 'Referral Form'],
            'steps' => [
                [
                    'title' => 'Referrer Details',
                    'fields' => [
                        $this->field('fname', 'First Name', 'text'),
                        $this->field('lname', 'Last Name', 'text'),
                        $this->field('email', 'Email', 'email'),
                        $this->field('mobile_number', 'Mobile', 'tel'),
                    ],
                ],
                [
                    'title' => 'Student Details',
                    'fields' => [
                        $this->field('student_fname', 'Student First Name', 'text'),
                        $this->field('student_lname', 'Student Last Name', 'text'),
                        $this->field('student_dob', 'Date of Birth', 'date'),
                        $this->field('student_start_date', 'Start Date', 'date'),
                    ],
                ],
                [
                    'title' => 'Additional Information',
                    'fields' => [
                        $this->field('details3', 'Tell us about the referral', 'textarea', ['col_span' => 2]),
                    ],
                ],
            ],
        ];
    }

    private function debitForm(): array
    {
        return [
            'name' => 'Direct Debit Form',
            'slug' => 'debit-form',
            'legacy_route' => '/debit-form',
            'legacy_table' => 'debits',
            'success_route' => '/debit/success',
            'submit_method' => 'urlencoded',
            'show_on_landing' => true,
            'hero_label' => 'Debit Form',
            'hero_variant' => 'outline',
            'sort_order' => 5,
            'settings' => ['heading' => 'Direct Debit Form'],
            'steps' => [
                [
                    'title' => 'Personal Information',
                    'fields' => [
                        $this->field('forename', 'Forename', 'text', ['required' => true]),
                        $this->field('surename', 'Surname', 'text', ['required' => true]),
                        $this->field('p_country', 'Nationality', 'select', ['required' => true, 'options_source' => 'countries', 'col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Contact Information',
                    'fields' => [
                        $this->field('street_address', 'Street Address', 'text', ['col_span' => 2]),
                        $this->field('address', 'Apartment, suite, etc', 'text', ['col_span' => 2]),
                        $this->field('city', 'City', 'text'),
                        $this->field('state', 'State/Province', 'text'),
                        $this->field('zip_code', 'Zip/Postal Code', 'text'),
                        $this->field('c_country', 'Country', 'select', ['options_source' => 'countries']),
                        $this->field('email', 'Email', 'email', ['required' => true]),
                        $this->field('confirm_email', 'Confirm Email', 'email', ['required' => true]),
                        $this->field('mobile_number', 'Mobile Number', 'tel', ['col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Bank Details',
                    'fields' => [
                        $this->field('bank_name', 'Bank Name', 'text', ['col_span' => 2]),
                        $this->field('account_number', 'Account Number', 'text'),
                        $this->field('sort_code', 'Sort Code', 'text'),
                        $this->field('debit_date', 'Debit Date', 'date', ['col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Students',
                    'fields' => [
                        $this->field('students', 'Students', 'repeater', [
                            'col_span' => 2,
                            'settings' => [
                                'fields' => [
                                    ['key' => 'name', 'label' => 'Student Name', 'type' => 'text'],
                                    ['key' => 'group', 'label' => 'Group', 'type' => 'select', 'options_source' => 'debit_groups'],
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    'title' => 'Declaration',
                    'fields' => [
                        $this->field('declaration', 'Declaration', 'checkbox', [
                            'required' => true,
                            'col_span' => 2,
                            'settings' => ['text' => 'I authorise Al-Rushd to collect payments via Direct Debit and confirm the information is accurate.'],
                        ]),
                    ],
                ],
            ],
        ];
    }

    private function jobForm(): array
    {
        return [
            'name' => 'Job Application',
            'slug' => 'job-applications',
            'legacy_route' => '/job-applications',
            'legacy_table' => 'job_applications',
            'success_route' => '/job-applications-success',
            'submit_method' => 'multipart',
            'show_on_landing' => true,
            'hero_label' => 'Job Application',
            'hero_variant' => 'gold',
            'sort_order' => 1,
            'settings' => ['heading' => 'Job Application Form', 'wide' => true],
            'steps' => [
                [
                    'title' => 'Personal Details',
                    'fields' => [
                        $this->field('name', 'Full Name', 'text', ['required' => true]),
                        $this->field('email', 'Email Address', 'email', ['required' => true]),
                        $this->field('phone', 'Phone Number', 'tel'),
                        $this->field('dob', 'Date of Birth', 'date'),
                        $this->field('gender', 'Gender', 'select', ['required' => true, 'options_source' => 'genders']),
                        $this->field('marital_status', 'Marital Status', 'select', ['required' => true, 'options_source' => 'job_marital']),
                        $this->field('ethnicity', 'Ethnicity', 'select', ['required' => true, 'options_source' => 'ethnicity']),
                        $this->field('religion', 'Religion', 'text'),
                        $this->field('nationality', 'Nationality', 'select', ['required' => true, 'options_source' => 'countries']),
                        $this->field('address', 'Street Address', 'text', ['required' => true]),
                        $this->field('city', 'City', 'text', ['required' => true]),
                        $this->field('state', 'State/Province', 'text', ['required' => true]),
                        $this->field('postal_code', 'Zip/Postal Code', 'text', ['required' => true]),
                        $this->field('country_of_residence', 'Country of Residence', 'select', ['required' => true, 'options_source' => 'countries', 'col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Position Information',
                    'fields' => [
                        $this->field('position_applied_for', 'Position Applied For', 'select', ['required' => true, 'options_source' => 'job_positions', 'col_span' => 2]),
                        $this->field('department', 'Department / School Section', 'select', ['required' => true, 'options_source' => 'job_departments']),
                        $this->field('type_of_employment', 'Type of Employment', 'select', ['options_source' => 'employment_types']),
                        $this->field('start_date', 'Available Start Date', 'date', ['required' => true]),
                        $this->field('preferred_working_hours', 'Preferred Working Hours', 'select', ['required' => true, 'options_source' => 'working_hours']),
                        $this->field('expected_salary', 'Expected Salary (optional)', 'text'),
                        $this->field('about_this_job', 'How did you hear about this job?', 'select', ['required' => true, 'options_source' => 'hear_about_job', 'col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Education & Qualifications',
                    'fields' => [
                        $this->field('education', 'Education Details', 'repeater', [
                            'col_span' => 2,
                            'settings' => [
                                'fields' => [
                                    ['key' => 'qualification', 'label' => 'Qualification', 'type' => 'text', 'required' => true],
                                    ['key' => 'subject', 'label' => 'Subject / Field of Study', 'type' => 'text', 'required' => true],
                                    ['key' => 'institution', 'label' => 'Institution Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'graduation_year', 'label' => 'Graduation Date', 'type' => 'date', 'required' => true],
                                ],
                            ],
                        ]),
                        $this->field('teaching_qualification', 'Teaching Qualification', 'text', ['required' => true]),
                        $this->field('relevant_certificates', 'Other Relevant Certificates', 'text', ['required' => true]),
                        $this->field('upload_certificates', 'Upload Certificates', 'file', ['col_span' => 2, 'settings' => ['multiple' => true]]),
                    ],
                ],
                [
                    'title' => 'Work Experience',
                    'fields' => [
                        $this->field('previous_employment', 'Employment History', 'repeater', [
                            'col_span' => 2,
                            'settings' => [
                                'fields' => [
                                    ['key' => 'current', 'label' => 'Employer', 'type' => 'text'],
                                    ['key' => 'position_held', 'label' => 'Position Held', 'type' => 'text'],
                                    ['key' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                                    ['key' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                                    ['key' => 'responsibilities', 'label' => 'Responsibilities', 'type' => 'textarea'],
                                    ['key' => 'reason_for_leaving', 'label' => 'Reason for Leaving', 'type' => 'text'],
                                ],
                            ],
                        ]),
                        $this->field('cv', 'Upload CV', 'file', ['col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Skills & Competencies',
                    'fields' => [
                        $this->field('subject_skill', 'Subjects / Areas you can teach', 'text'),
                        $this->field('languages_spoken', 'Languages Spoken', 'text'),
                        $this->field('level_of_computer', 'Level of Computer Literacy', 'text'),
                        $this->field('microsoft_teams', 'Microsoft Teams / Zoom Experience', 'text'),
                        $this->field('online_teaching_experience', 'Online Teaching Experience', 'text'),
                        $this->field('years_of_teaching_xperience', 'Years of Teaching', 'text'),
                        $this->field('other_relevant_skills', 'Other Relevant Skills', 'textarea', ['col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'References',
                    'fields' => [
                        $this->field('references', 'References', 'repeater', [
                            'col_span' => 2,
                            'settings' => [
                                'fields' => [
                                    ['key' => 'refer_name', 'label' => 'Name', 'type' => 'text'],
                                    ['key' => 'refer_email', 'label' => 'Email', 'type' => 'email'],
                                    ['key' => 'refer_phone', 'label' => 'Phone', 'type' => 'tel'],
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    'title' => 'Compliance & Documents',
                    'fields' => [
                        $this->field('right_to_work', 'Right to Work in UK', 'select', ['options_source' => 'yes_no']),
                        $this->field('insurance_number', 'National Insurance Number', 'text'),
                        $this->field('dbs_certificate', 'DBS Certificate', 'select', ['options_source' => 'yes_no']),
                        $this->field('dbs', 'Upload DBS Certificate', 'file'),
                        $this->field('dbs_issue_date', 'DBS Issue Date', 'date'),
                        $this->field('background_check', 'Background Check Consent', 'select', ['options_source' => 'yes_no']),
                        $this->field('notice_period', 'Notice Period', 'text'),
                        $this->field('special_requirement', 'Special Requirements', 'textarea', ['col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Declaration',
                    'fields' => [
                        $this->field('personal_statement', 'Personal Statement', 'textarea', ['col_span' => 2]),
                        $this->field('true_and_complete', 'Information is true and complete', 'checkbox', ['required' => true, 'col_span' => 2]),
                        $this->field('recruitment_purposes', 'Consent for recruitment purposes', 'checkbox', ['required' => true, 'col_span' => 2]),
                    ],
                ],
            ],
        ];
    }

    private function staffForm(): array
    {
        return [
            'name' => 'Staff Application',
            'slug' => 'staff-application',
            'legacy_route' => '/staff-application',
            'legacy_table' => 'staff_admission_forms',
            'success_route' => '/staff-application-success',
            'submit_method' => 'multipart',
            'show_on_landing' => true,
            'hero_label' => 'Staff Admission',
            'hero_variant' => 'gold',
            'sort_order' => 2,
            'settings' => ['heading' => 'Staff Application Form'],
            'steps' => [
                [
                    'title' => 'Candidate Details',
                    'fields' => [
                        $this->field('job_applied_for', 'Job Applied For', 'select', ['required' => true, 'options_source' => 'job_positions']),
                        $this->field('forename', 'Forename', 'text', ['required' => true]),
                        $this->field('middle_names', 'Middle Names', 'text'),
                        $this->field('surname', 'Surname', 'text', ['required' => true]),
                        $this->field('preferred_name', 'Preferred Name', 'text'),
                        $this->field('date_of_birth', 'Date of Birth', 'date'),
                        $this->field('gender', 'Gender', 'radio', ['options_source' => 'genders']),
                        $this->field('marital_status', 'Marital Status', 'radio', ['options_source' => 'staff_marital']),
                        $this->field('ethnicity', 'Ethnicity', 'select', ['required' => true, 'options_source' => 'ethnicity']),
                        $this->field('religion', 'Religion', 'text'),
                        $this->field('nationality', 'Nationality', 'select', ['required' => true, 'options_source' => 'countries']),
                        $this->field('mobile_number', 'Mobile Number', 'tel', ['required' => true]),
                        $this->field('home_telephone', 'Home Telephone', 'tel'),
                        $this->field('email', 'Email', 'email', ['required' => true, 'col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Address',
                    'fields' => [
                        $this->field('street_address', 'Street Address', 'text', ['col_span' => 2]),
                        $this->field('address_line_2', 'Address Line 2', 'text', ['col_span' => 2]),
                        $this->field('city', 'City', 'text'),
                        $this->field('county_state_region', 'County/State/Region', 'text'),
                        $this->field('zip_postal_code', 'Zip/Postal Code', 'text'),
                        $this->field('country', 'Country', 'select', ['options_source' => 'countries']),
                        $this->field('uk_work', 'Allowed to work in the UK?', 'select', ['options_source' => 'yes_no']),
                        $this->field('dbs', 'Cleared DBS?', 'select', ['options_source' => 'yes_no']),
                    ],
                ],
                [
                    'title' => 'Documents',
                    'fields' => [
                        $this->field('profile_photo', 'Profile Photo', 'file'),
                        $this->field('prof_of_id', 'Proof of ID', 'file'),
                        $this->field('prof_of_address', 'Proof of Address', 'file'),
                        $this->field('dbs_one', 'DBS Document', 'file'),
                        $this->field('cv', 'CV', 'file'),
                        $this->field('certificated', 'Certificates', 'repeater', [
                            'col_span' => 2,
                            'settings' => [
                                'fields' => [
                                    ['key' => 'title', 'label' => 'Certificate Title', 'type' => 'text'],
                                    ['key' => 'file', 'label' => 'Certificate File', 'type' => 'file'],
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    'title' => 'Bank Information',
                    'fields' => [
                        $this->field('bank_type', 'Bank Type', 'radio', ['options' => ['uk' => 'UK Bank', 'international' => 'International']]),
                        $this->field('uk_account_name', 'UK Account Name', 'text'),
                        $this->field('uk_bank_name', 'UK Bank Name', 'text'),
                        $this->field('uk_account_number', 'UK Account Number', 'text'),
                        $this->field('sort_code', 'Sort Code', 'text'),
                        $this->field('international_country_name', 'International Country', 'text'),
                        $this->field('international_account_name', 'International Account Name', 'text'),
                        $this->field('international_bank_name', 'International Bank Name', 'text'),
                        $this->field('international_account_number', 'International Account Number', 'text'),
                        $this->field('swift_code', 'SWIFT Code', 'text'),
                    ],
                ],
                [
                    'title' => 'Emergency Contact',
                    'fields' => [
                        $this->field('emergency_forename', 'Forename', 'text'),
                        $this->field('emergency_surname', 'Surname', 'text'),
                        $this->field('contact_email', 'Email', 'email'),
                        $this->field('contact_phone', 'Phone', 'tel'),
                        $this->field('contact_address', 'Address', 'textarea', ['col_span' => 2]),
                    ],
                ],
                [
                    'title' => 'Terms & Signature',
                    'fields' => [
                        $this->field('terms', 'Accept terms and conditions', 'checkbox', ['required' => true, 'col_span' => 2]),
                        $this->field('signature', 'Signature', 'file', ['required' => true, 'col_span' => 2, 'settings' => ['accept' => 'image/*']]),
                    ],
                ],
            ],
        ];
    }

    private function meetingForm(): array
    {
        return [
            'name' => 'Meeting / Book a Call',
            'slug' => 'meeting-form',
            'legacy_route' => '/book-a-call',
            'legacy_table' => 'mettings',
            'success_route' => '/',
            'submit_method' => 'urlencoded',
            'show_on_landing' => false,
            'handler' => 'custom',
            'sort_order' => 20,
            'settings' => ['heading' => 'Book a Call', 'custom_component' => 'book-a-call'],
            'steps' => [
                [
                    'title' => 'Meeting Details',
                    'fields' => [
                        $this->field('name', 'Name', 'text', ['required' => true]),
                        $this->field('email', 'Email', 'email', ['required' => true]),
                        $this->field('guest_email', 'Guest Email', 'email'),
                        $this->field('location', 'Location', 'text'),
                        $this->field('message', 'Message', 'textarea', ['col_span' => 2]),
                        $this->field('date', 'Date', 'date'),
                        $this->field('time', 'Time', 'text'),
                        $this->field('timezone', 'Timezone', 'text'),
                    ],
                ],
            ],
        ];
    }

    private function titleOptions(): array
    {
        return ['Mr', 'Mrs', 'Miss', 'Ms', 'Mx', 'Dr', 'Prof', 'Rev', 'Sir', 'Dame', 'Lady', 'Lord'];
    }

    private function studentAdmissionForm(): array
    {
        $titles = $this->titleOptions();

        return [
            'name' => 'Student Admission',
            'slug' => 'student-admission',
            'legacy_route' => '/student-admission/step/1',
            'success_route' => '/forms/student-admission/success',
            'submit_method' => 'multipart',
            'show_on_landing' => true,
            'hero_label' => 'Student Admission',
            'hero_variant' => 'outline',
            'handler' => 'dynamic',
            'sort_order' => 3,
            'settings' => [
                'heading' => 'Student Admission',
                'wide' => true,
                'description' => 'Register your child at Al-Rushd Independent School.',
            ],
            'steps' => [
                [
                    'title' => 'School Selection',
                    'description' => 'Select the school you wish to apply to.',
                    'fields' => [
                        $this->field('selected_school', 'School', 'select', [
                            'required' => true,
                            'col_span' => 2,
                            'options_source' => 'schools',
                            'help_text' => 'Choose the Al-Rushd school location for your application.',
                        ]),
                    ],
                ],
                [
                    'title' => 'Parent Details',
                    'description' => 'Primary parent or guardian contact information.',
                    'fields' => [
                        $this->field('_section_primary_parent', 'Primary Parent / Guardian', 'section', ['col_span' => 2]),
                        $this->field('title', 'Title', 'select', ['required' => true, 'options' => $titles]),
                        $this->field('fname', 'First Name', 'text', ['required' => true]),
                        $this->field('lname', 'Last Name', 'text', ['required' => true]),
                        $this->field('relationship', 'Relationship to Student(s)', 'select', [
                            'required' => true,
                            'col_span' => 2,
                            'options_source' => 'relationships',
                        ]),
                        $this->field('email', 'Email Address', 'email', ['required' => true]),
                        $this->field('confirm_email', 'Confirm Email', 'email', ['required' => true]),
                        $this->field('mobile_number', 'Mobile Number', 'tel', ['required' => true]),
                        $this->field('home_telephone', 'Home Telephone', 'tel', ['required' => true]),
                        $this->field('work_number', 'Work Number', 'tel'),
                        $this->field('address', 'Street Address', 'text', ['required' => true, 'col_span' => 2]),
                        $this->field('apartment', 'Apartment / Suite', 'text', ['col_span' => 2]),
                        $this->field('city', 'City', 'text', ['required' => true]),
                        $this->field('province', 'State / Province', 'text'),
                        $this->field('postal_code', 'Zip / Postal Code', 'text', ['required' => true]),
                        $this->field('country', 'Country', 'select', ['required' => true, 'options_source' => 'countries']),
                        $this->field('file1', 'Proof of ID', 'file', ['required' => true]),
                        $this->field('file2', 'Proof of Address', 'file', ['required' => true]),
                        $this->field('_section_secondary_parent', 'Secondary Parent / Guardian', 'section', ['col_span' => 2]),
                        $this->field('secondary_title', 'Title', 'select', ['options' => $titles]),
                        $this->field('secondary_fname', 'First Name', 'text'),
                        $this->field('secondary_lname', 'Last Name', 'text'),
                        $this->field('secondary_relationship', 'Relationship to Student(s)', 'select', [
                            'col_span' => 2,
                            'options_source' => 'relationships',
                        ]),
                        $this->field('secondary_email', 'Email Address', 'email'),
                        $this->field('secondary_confirm_email', 'Confirm Email', 'email'),
                        $this->field('secondary_mobile_number', 'Mobile Number', 'tel'),
                        $this->field('secondary_home_telephone', 'Home Telephone', 'tel'),
                        $this->field('secondary_work_number', 'Work Number', 'tel'),
                        $this->field('secondary_address', 'Street Address', 'text', ['col_span' => 2]),
                        $this->field('secondary_apartment', 'Apartment / Suite', 'text', ['col_span' => 2]),
                        $this->field('secondary_city', 'City', 'text'),
                        $this->field('secondary_province', 'State / Province', 'text'),
                        $this->field('secondary_postal_code', 'Zip / Postal Code', 'text'),
                        $this->field('secondary_country', 'Country', 'select', ['options_source' => 'countries', 'col_span' => 2]),
                        $this->field('file3', 'Secondary — Proof of ID', 'file'),
                        $this->field('file4', 'Secondary — Proof of Address', 'file'),
                    ],
                ],
                [
                    'title' => 'Student Details',
                    'description' => 'Tell us about each child you are registering.',
                    'fields' => [
                        $this->field('students', 'Students', 'repeater', [
                            'required' => true,
                            'col_span' => 2,
                            'settings' => [
                                'fields' => [
                                    ['key' => 'fname', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'lname', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'required' => true],
                                    ['key' => 'gender', 'label' => 'Gender', 'type' => 'select', 'required' => true, 'options_source' => 'genders'],
                                    ['key' => 'nationality', 'label' => 'Nationality', 'type' => 'select', 'required' => true, 'options_source' => 'nationalities'],
                                    ['key' => 'start_date', 'label' => 'Desired Start Date', 'type' => 'select', 'required' => true, 'options_source' => 'admission_dates'],
                                    ['key' => 'year_group', 'label' => 'Year Group', 'type' => 'select', 'required' => true, 'options_source' => 'student_years'],
                                    ['key' => 'package', 'label' => 'Package', 'type' => 'select', 'required' => true, 'options_source' => 'student_packages'],
                                    ['key' => 'student_file1', 'label' => 'Proof of ID', 'type' => 'file', 'required' => true],
                                    ['key' => 'student_file2', 'label' => 'Previous Academic Report', 'type' => 'file', 'required' => true],
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    'title' => 'Additional Information',
                    'description' => 'Education, health and access information.',
                    'fields' => [
                        $this->field('health_care', 'Does the child have an Education Health Care Plan (EHCP)?', 'radio', [
                            'required' => true,
                            'col_span' => 2,
                            'options' => ['Yes', 'No'],
                        ]),
                        $this->field('previus_school', 'Has the child been permanently excluded from a previous school?', 'radio', [
                            'required' => true,
                            'col_span' => 2,
                            'options' => ['Yes', 'No'],
                        ]),
                        $this->field('access_protocol', 'Fair Access Protocol category', 'textarea', [
                            'required' => true,
                            'col_span' => 2,
                            'placeholder' => 'Describe which category applies, or enter None',
                        ]),
                        $this->field('authority', 'Supporting local authority', 'text', ['col_span' => 2]),
                        $this->field('assigned', 'Assigned social worker name', 'text', ['col_span' => 2]),
                        $this->field('special_education', 'On SEND code of practice?', 'radio', [
                            'required' => true,
                            'options' => ['Yes', 'No'],
                        ]),
                        $this->field('medical_condition', 'Long-term medical conditions?', 'radio', [
                            'required' => true,
                            'options' => ['Yes', 'No'],
                        ]),
                        $this->field('direct_placement', 'Directed to Alternative Provision?', 'radio', [
                            'required' => true,
                            'options' => ['Yes', 'No'],
                        ]),
                        $this->field('placement_detail', 'Alternative provision details', 'text', ['col_span' => 2]),
                        $this->field('percentage', 'Attendance percentage at previous school', 'text', ['required' => true]),
                    ],
                ],
                [
                    'title' => 'Package Selection',
                    'description' => 'Confirm your preferred pricing package for each student.',
                    'fields' => [
                        $this->field('package_notes', 'Package preferences', 'textarea', [
                            'col_span' => 2,
                            'placeholder' => 'Add any notes about your preferred package (Essentials, Standard, Premium, etc.)',
                        ]),
                        $this->field('preferred_package', 'Preferred package type', 'select', [
                            'col_span' => 2,
                            'options_source' => 'student_packages',
                        ]),
                    ],
                ],
                [
                    'title' => 'Review & Signature',
                    'description' => 'Review your application and sign to confirm.',
                    'fields' => [
                        $this->field('signature_accept', 'I agree to the Terms & Conditions', 'checkbox', [
                            'required' => true,
                            'col_span' => 2,
                            'settings' => ['text' => 'I have read and agree to the Terms & Conditions.'],
                        ]),
                        $this->field('accpet', 'Admission process consent', 'checkbox', [
                            'required' => true,
                            'col_span' => 2,
                            'settings' => ['text' => 'I have read and understood the admission process and agree with the Terms and Conditions of Al-Rushd Independent School.'],
                        ]),
                        $this->field('signature', 'Digital signature', 'file', [
                            'required' => true,
                            'col_span' => 2,
                            'settings' => ['accept' => 'image/*'],
                            'help_text' => 'Upload a signed declaration or signature image.',
                        ]),
                    ],
                ],
                [
                    'title' => 'Payment',
                    'description' => 'Pay the application processing fee.',
                    'fields' => [
                        $this->field('application_payment', 'Application Payment', 'payment', [
                            'required' => true,
                            'col_span' => 2,
                            'help_text' => 'Pay the £15 application fee securely online or request offline payment.',
                            'settings' => [
                                'amount' => 15,
                                'currency' => 'gbp',
                                'fee_label' => 'Application Fee',
                                'allow_stripe' => true,
                                'allow_offline' => true,
                                'show_summary' => true,
                            ],
                        ]),
                    ],
                ],
            ],
        ];
    }
}
