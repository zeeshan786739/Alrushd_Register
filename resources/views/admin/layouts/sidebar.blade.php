<ul class="sidebar-menu" id="sidebar-menu">

    @canany(['view dashboard'])
    <li>
        <a href="{{ route('admin.dashboard') }}">
            <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
            <span>Dashboard</span>
        </a>
    </li>
    @endcanany

    <li class="sidebar-menu-group-title ms-1">Application</li>

    {{-- ================= USER MANAGEMENT ================= --}}
    @canany([
    'create role','edit role','view role','delete role',
    'create permission','edit permission','view permission','delete permission',
    'create user','edit user','view user','delete user'
    ])
    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:account-cog" class="menu-icon"></iconify-icon>
            <span>User Management</span>
        </a>
        <ul class="sidebar-submenu">

            @canany(['create role','edit role','view role','delete role'])
            <li>
                <a href="{{ route('admin.roles.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600"></i> Role List
                </a>
            </li>
            @endcanany

            @canany(['create permission','edit permission','view permission','delete permission'])
            <li>
                <a href="{{ route('admin.permissions.index') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600"></i> Permission List
                </a>
            </li>
            @endcanany

            @canany(['create user','edit user','view user','delete user'])
            <li>
                <a href="{{ route('admin.users.index') }}">
                    <i class="ri-circle-fill circle-icon text-info-main"></i> Users List
                </a>
            </li>
            @endcanany

        </ul>
    </li>
    @endcanany


    {{-- ================= OPEN EVENTS ================= --}}
    @canany([
    'create open_event','edit open_event','view open_event','delete open_event',
    'create event_item','edit event_item','view event_item','delete event_item',
    'create meet_speakers','edit meet_speakers','view meet_speakers','delete meet_speakers',
    'create open_event_form','edit open_event_form','view open_event_form','delete open_event_form'
    ])
    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:calendar-month" class="menu-icon"></iconify-icon>
            <span>Open Events</span>
        </a>
        <ul class="sidebar-submenu">

            @canany(['create open_event','edit open_event','view open_event','delete open_event'])
            <li>
                <a href="{{ route('admin.open-events.index') }}">
                    <iconify-icon icon="mdi:calendar-star" class="menu-icon"></iconify-icon>
                    <span>Open Events</span>
                </a>
            </li>
            @endcanany

            @canany(['create event_item','edit event_item','view event_item','delete event_item'])
            <li>
                <a href="{{ route('admin.open-event-items.index') }}">
                    <iconify-icon icon="mdi:format-list-bulleted" class="menu-icon"></iconify-icon>
                    <span>Open Event Items</span>
                </a>
            </li>
            @endcanany

            @canany(['create meet_speakers','edit meet_speakers','view meet_speakers','delete meet_speakers'])
            <li>
                <a href="{{ route('admin.meet-speakers.index') }}">
                    <iconify-icon icon="mdi:account-voice" class="menu-icon"></iconify-icon>
                    <span>Meet Speakers</span>
                </a>
            </li>
            @endcanany

            @canany(['create open_event_form','edit open_event_form','view open_event_form','delete open_event_form'])
            <li>
                <a href="{{ route('admin.open-event-form.index') }}">
                    <iconify-icon icon="mdi:form-select" class="menu-icon"></iconify-icon>
                    <span>Submissions Form</span>
                </a>
            </li>
            @endcanany

        </ul>
    </li>
    @endcanany


    {{-- ================= ADMISSION FORM ================= --}}
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
    'create admission_studetns','edit admission_studetns','view admission_studetns','delete admission_studetns'
    ])
    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:school-outline" class="menu-icon"></iconify-icon>
            <span>Admission Form</span>
        </a>
        <ul class="sidebar-submenu">

            @canany(['create nationality','edit nationality','view nationality','delete nationality'])
            <li>
                <a href="{{ route('admin.nationality.index') }}">
                    <iconify-icon icon="mdi:flag" class="menu-icon"></iconify-icon>
                    <span>Nationality</span>
                </a>
            </li>
            @endcanany

            @canany(['create admission_date','edit admission_date','view admission_date','delete admission_date'])
            <li>
                <a href="{{ route('admin.admission-date.index') }}">
                    <iconify-icon icon="mdi:calendar-today" class="menu-icon"></iconify-icon>
                    <span>Admission Date</span>
                </a>
            </li>
            @endcanany

            @canany(['create gender','edit gender','view gender','delete gender'])
            <li>
                <a href="{{ route('admin.genders.index') }}">
                    <iconify-icon icon="mdi:gender-male-female" class="menu-icon"></iconify-icon>
                    <span>Gender</span>
                </a>
            </li>
            @endcanany

            @canany(['create relation_ship','edit relation_ship','view relation_ship','delete relation_ship'])
            <li>
                <a href="{{ route('admin.relation-ships.index') }}">
                    <iconify-icon icon="mdi:link" class="menu-icon"></iconify-icon>
                    <span>Relation Ships</span>
                </a>
            </li>
            @endcanany

            @canany(['create country','edit country','view country','delete country'])
            <li>
                <a href="{{ route('admin.countries.index') }}">
                    <iconify-icon icon="mdi:flag" class="menu-icon"></iconify-icon>
                    <span>Payment Country</span>
                </a>
            </li>
            @endcanany

            @canany(['create terms_condition','edit terms_condition','view terms_condition','delete terms_condition'])
            <li>
                <a href="{{ route('admin.terms.index') }}">
                    <iconify-icon icon="mdi:file-document" class="menu-icon"></iconify-icon>
                    <span>Terms & Condition</span>
                </a>
            </li>
            @endcanany

            @canany(['create school','edit school','view school','delete school'])
            <li>
                <a href="{{ route('admin.student-school.index') }}">
                    <iconify-icon icon="mdi:school" class="menu-icon"></iconify-icon>
                    <span>School</span>
                </a>
            </li>
            @endcanany

            @canany(['create year','edit year','view year','delete year'])
            <li>
                <a href="{{ route('admin.student-years.index') }}">
                    <iconify-icon icon="mdi:calendar-clock" class="menu-icon"></iconify-icon>
                    <span>Year</span>
                </a>
            </li>
            @endcanany

            {{--<li>
                <a href="{{ route('admin.student-groups.index') }}">
            <iconify-icon icon="mdi:account-group-outline" class="menu-icon"></iconify-icon>
            <span>Group</span>
            </a>
    </li>--}}

    @canany(['create language','edit language','view language','delete language'])
    <li>
        <a href="{{ route('admin.student-language.index') }}">
            <iconify-icon icon="mdi:translate" class="menu-icon"></iconify-icon>
            <span>Language</span>
        </a>
    </li>
    @endcanany

    @canany(['create subject','edit subject','view subject','delete subject'])
    <li>
        <a href="{{ route('admin.student-subject.index') }}">
            <iconify-icon icon="mdi:book-open-page-variant-outline" class="menu-icon"></iconify-icon>
            <span>Subject</span>
        </a>
    </li>
    @endcanany

    @canany(['create package','edit package','view package','delete package'])
    <li>
        <a href="{{ route('admin.student-package.index') }}">
            <iconify-icon icon="mdi:package-variant-closed" class="menu-icon"></iconify-icon>
            <span>Package</span>
        </a>
    </li>
    @endcanany

    @canany(['create course','edit course','view course','delete course'])
    <li>
        <a href="{{ route('admin.student-course.index') }}">
            <iconify-icon icon="mdi:book-open-variant" class="menu-icon"></iconify-icon>
            <span>Course</span>
        </a>
    </li>
    @endcanany

    @canany(['create admission_studetns','edit admission_studetns','view admission_studetns','delete admission_studetns'])
    <li>
        <a href="{{ route('admin.form-students.index') }}">
            <iconify-icon icon="mdi:account-school" class="menu-icon"></iconify-icon>
            <span>Form Submission</span>
        </a>
    </li>
    @endcanany

</ul>
</li>
@endcanany




@canany([
'create staff_application_form','edit staff_application_form','view staff_application_form','delete staff_application_form'
])
<li class="dropdown">
    <a href="javascript:void(0)">
        <iconify-icon icon="mdi:user-circle" class="menu-icon"></iconify-icon>
        <span>Staff Application</span>
    </a>
    <ul class="sidebar-submenu">
    

        {{-- Staff Application Form --}}
        @canany(['create staff_application_form','edit staff_application_form','view staff_application_form','delete staff_application_form'])
        <li>
            <a href="{{ route('admin.staff-applications-form.index') }}">
                <iconify-icon icon="ic:baseline-person" class="menu-icon"></iconify-icon>
                <span>Staff Application Form</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.staff-terms-condition') }}">
                <iconify-icon icon="ic:baseline-person" class="menu-icon"></iconify-icon>
                <span>Staff Terms & Conditions</span>
            </a>
        </li>
        @endcanany


      

    </ul>
</li>
@endcanany



@canany([
'create metting_form','edit metting_form','view metting_form','delete metting_form',
'create debit_form','edit debit_form','view debit_form','delete debit_form',
'create enquire_form','edit enquire_form','view enquire_form','delete enquire_form',
'create referal_form','edit referal_form','view referal_form','delete referal_form'
])
<li class="dropdown">
    <a href="javascript:void(0)">
        <iconify-icon icon="mdi:form-select" class="menu-icon" title="Form Submissions"></iconify-icon>
        <span>Form Submissions</span>
    </a>
    <ul class="sidebar-submenu">
    
     {{-- Staff Application Form --}}
        @canany(['create staff_application_form','edit staff_application_form','view staff_application_form','delete staff_application_form'])
        <li>
            <a href="{{ route('admin.job-applications-form.index') }}">
                <iconify-icon icon="ic:baseline-person" class="menu-icon"></iconify-icon>
                <span>Job Application Form</span>
            </a>
        </li>
        @endcanany

        {{-- Staff Application Form --}}
       {{--@canany(['create staff_application_form','edit staff_application_form','view staff_application_form','delete staff_application_form'])
        <li>
            <a href="{{ route('admin.staff-applications-form.index') }}">
                <iconify-icon icon="ic:baseline-person" class="menu-icon"></iconify-icon>
                <span>Staff Application Form</span>
            </a>
        </li>
        @endcanany--}} 

        {{-- Metting Form --}}
        @canany(['create metting_form','edit metting_form','view metting_form','delete metting_form'])
        <li>
            <a href="{{ route('admin.metting-form.index') }}">
                <iconify-icon icon="ic:baseline-meeting-room" class="menu-icon"></iconify-icon>
                <span>Metting Form</span>
            </a>
        </li>
        @endcanany

        {{-- Debit Form --}}
        @canany(['create debit_form','edit debit_form','view debit_form','delete debit_form'])
        <li>
            <a href="{{ route('admin.debit-forms.index') }}">
                <iconify-icon icon="mdi:cash" class="menu-icon" style="font-size:24px;"></iconify-icon>
                <span>Debit Forms</span>
            </a>
        </li>
        @endcanany

        {{-- Enquire Form --}}
        @canany(['create enquire_form','edit enquire_form','view enquire_form','delete enquire_form'])
        <li>
            <a href="{{ route('admin.enquires.index') }}">
                <iconify-icon icon="mdi:clipboard-text-outline" class="menu-icon" style="font-size:24px;"></iconify-icon>
                <span>Enquire Now</span>
            </a>
        </li>
        @endcanany

        {{-- Referral Form --}}
        @canany(['create referal_form','edit referal_form','view referal_form','delete referal_form'])
        <li>
            <a href="{{ route('admin.referrals.index') }}">
                <iconify-icon icon="mdi:handshake-outline" class="menu-icon" style="font-size:24px;"></iconify-icon>
                <span>Referral</span>
            </a>
        </li>
        @endcanany

    </ul>
</li>
@endcanany



<li class="dropdown">
    <a href="javascript:void(0)">
        <iconify-icon icon="mdi:form-select" class="menu-icon" title="Form Submissions"></iconify-icon>
        <span>Api Form Applications</span>
    </a>
    <ul class="sidebar-submenu">
        @can('view job')
        <li>
            <a href="{{ route('admin.job-applications') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Job Applications
            </a>
        </li>
        @endcan
        <!-- @can('view staff')
            <li>
                <a href="{{ route('admin.staff-applications') }}">
                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Staff Applications
                </a>
            </li>
              @endcan -->
        @can('view apply')
        <li>
            <a href="{{ route('admin.apply-now') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Apply Now
            </a>
        </li>
        @endcan

        @can('view apply')
        <li>
            <a href="{{ route('admin.online-madrasah') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Online Madrasah
            </a>
        </li>
        @endcan

        @can('view enquire')
        <li>
            <a href="{{ route('admin.enquire-now') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Enquire Now
            </a>
        </li>
        @endcan

        @can('view referral')
        <li>
            <a href="{{ route('admin.referral-applications') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Referral
            </a>
        </li>
        <li>
            <a href="{{ route('admin.direct-debit') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Direct Debit
            </a>
        </li>
        @endcan

        @can('view subscribe')
        <li>
            <a href="{{ route('admin.subscribe-applications') }}">
                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Subscribe
            </a>
        </li>
        @endcan




    </ul>
</li>

@can('view setting')
<li>
    <a href="{{ route('admin.settings.index') }}">
        <iconify-icon icon="solar:settings-outline" class="menu-icon"></iconify-icon>
        <span>Settings</span>
    </a>
</li>
@endcan



{{--@canany(['view group', 'view package','view plan' ,'view languages'])
    <li class="dropdown">
        <a href="javascript:void(0)">
            <iconify-icon icon="mdi:account-cog" class="menu-icon"></iconify-icon>
            <span>Package</span>
        </a>
        <ul class="sidebar-submenu">
            @can('view group')
            <li>
                <a href="{{ route('admin.groups.index') }}">
<i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Group List
</a>
</li>
@endcan

@can('view package')
<li>
    <a href="{{ route('admin.package.index') }}">
        <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Package List
    </a>
</li>
@endcan

@can('view plan')
<li>
    <a href="{{ route('admin.plans.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Plan List
    </a>
</li>
@endcan

@can('view languages')
<li>
    <a href="{{ route('admin.languages.index') }}">
        <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Languages
    </a>
</li>
@endcan

@can('view subjects')
<li>
    <a href="{{ route('admin.subjects.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Subjects
    </a>
</li>
@endcan

@can('view group')
<li>
    <a href="{{ route('admin.time-tables.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Time Table
    </a>
</li>
@endcan

@can('view group')
<li>
    <a href="{{ route('admin.group-years.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Group Year
    </a>
</li>
@endcan

@can('view qualifications')
<li>
    <a href="{{ route('admin.qualifications.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Qualifications
    </a>
</li>
@endcan


@can('view coursefee')
<li>
    <a href="{{ route('admin.course-fees.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Course Fee
    </a>
</li>
@endcan

@can('view coupon')
<li>
    <a href="{{ route('admin.coupons.index') }}">
        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Coupons
    </a>
</li>
@endcan


</ul>
</li>
@endcanany


@can('view student')
<li>
    <a href="{{ route('admin.admission-student-list.index') }}">
        <iconify-icon icon="mdi:account-school-outline" class="menu-icon"></iconify-icon>
        <span>Admission Student List</span>
    </a>
</li>
@endcan--}}



</ul>