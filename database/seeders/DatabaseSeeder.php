<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Setting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Setting::firstOrCreate([

        //     'company_name' =>'',
        // ]);


        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create Super Admin
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Change it in production
            ]
        );

        // Create super-admin role
        $role = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin']
        );

        // Define all permissions
        $permissions = [

            'create dashboard',
            'edit dashboard',
            'view dashboard',
            'delete dashboard',

            // role permissions
            'create role',
            'edit role',
            'view role',
            'delete role',

            // permission permissions
            'create permission',
            'edit permission',
            'view permission',
            'delete permission',


            // user permissions
            'create user',
            'edit user',
            'view user',
            'delete user',


            // nationality permissions
            'create nationality',
            'edit nationality',
            'view nationality',
            'delete nationality',


            // admission_date permissions
            'create admission_date',
            'edit admission_date',
            'view admission_date',
            'delete admission_date',


            // gender permissions
            'create gender',
            'edit gender',
            'view gender',
            'delete gender',


            // relation_ship permissions
            'create relation_ship',
            'edit relation_ship',
            'view relation_ship',
            'delete relation_ship',


            // country permissions
            'create country',
            'edit country',
            'view country',
            'delete country',


            // terms_condition permissions
            'create terms_condition',
            'edit terms_condition',
            'view terms_condition',
            'delete terms_condition',


            // school permissions
            'create school',
            'edit school',
            'view school',
            'delete school',

            // year permissions
            'create year',
            'edit year',
            'view year',
            'delete year',

            // language permissions
            'create language',
            'edit language',
            'view language',
            'delete language',

            // subject permissions
            'create subject',
            'edit subject',
            'view subject',
            'delete subject',


            // package permissions
            'create package',
            'edit package',
            'view package',
            'delete package',

            // course permissions
            'create course',
            'edit course',
            'view course',
            'delete course',


            // admission_studetns permissions
            'create admission_studetns',
            'edit admission_studetns',
            'view admission_studetns',
            'delete admission_studetns',


            // open_event permissions
            'create open_event',
            'edit open_event',
            'view open_event',
            'delete open_event',


            // event_item permissions
            'create event_item',
            'edit event_item',
            'view event_item',
            'delete event_item',


            // meet_speakers permissions
            'create meet_speakers',
            'edit meet_speakers',
            'view meet_speakers',
            'delete meet_speakers',

            // open_event_form permissions
            'create open_event_form',
            'edit open_event_form',
            'view open_event_form',
            'delete open_event_form',


            // staff_application_form permissions
            'create staff_application_form',
            'edit staff_application_form',
            'view staff_application_form',
            'delete staff_application_form',

            // metting_form permissions
            'create metting_form',
            'edit metting_form',
            'view metting_form',
            'delete metting_form',


            // debit_form permissions
            'create debit_form',
            'edit debit_form',
            'view debit_form',
            'delete debit_form',


            // enquire_form permissions
            'create enquire_form',
            'edit enquire_form',
            'view enquire_form',
            'delete enquire_form',


            // referal_form permissions
            'create referal_form',
            'edit referal_form',
            'view referal_form',
            'delete referal_form',

            // form center permissions
            'create form_center',
            'edit form_center',
            'view form_center',
            'delete form_center',


             // setting permissions
            'create setting',
            'edit setting',
            'view setting',
            'delete setting',



            // api_job_application permissions
            'view api_job_application',
            'view api_apply_now',
            'view api_online_madrasah',
            'view api_enquire_now',
            'view api_referral',
            'view api_subscribe_form',


        ];

        // Create and assign permissions to role
        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'admin'
            ]);

            // Assign permission to role if not already assigned
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        // Assign role to admin
        if (!$admin->hasRole($role)) {
            $admin->assignRole($role);
        }

        $this->call(FormDefinitionsSeeder::class);
        $this->call(MigrateLegacyFormSubmissionsSeeder::class);
    }
}
