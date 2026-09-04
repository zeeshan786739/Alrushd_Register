<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $templates = [
            [
                'name' => 'Welcome to Our School',
                'subject' => 'Welcome, {{name}}!',
                'category' => 'Welcome',
                'body_html' => '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#1f2937"><h1 style="color:#0f274a">Welcome to our school</h1><p>Hello {{name}},</p><p>We are delighted to welcome you to our school community. Our team is here to help you with every step of your journey.</p><p>Kind regards,<br>The Admissions Team</p></div>',
            ],
            [
                'name' => 'Open Day Invitation',
                'subject' => 'You are invited to our Open Day',
                'category' => 'Events',
                'body_html' => '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#1f2937"><h1 style="color:#0f274a">Discover our school</h1><p>Hello {{name}},</p><p>Join us for our upcoming Open Day. Meet our teachers, explore the campus, and learn about our programmes.</p><p><strong>Date:</strong> Add event date<br><strong>Time:</strong> Add event time<br><strong>Location:</strong> Add venue</p><p>We look forward to meeting you.</p></div>',
            ],
            [
                'name' => 'Application Reminder',
                'subject' => 'Reminder: complete your application',
                'category' => 'Admissions',
                'body_html' => '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#1f2937"><h1 style="color:#0f274a">Your application is waiting</h1><p>Hello {{name}},</p><p>This is a friendly reminder to complete your application. Please submit any outstanding information so our admissions team can continue reviewing it.</p><p>If you need assistance, simply reply to this email.</p></div>',
            ],
            [
                'name' => 'Enrollment Confirmation',
                'subject' => 'Your enrollment is confirmed',
                'category' => 'Enrollment',
                'body_html' => '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#1f2937"><h1 style="color:#0f274a">Enrollment confirmed</h1><p>Hello {{name}},</p><p>We are pleased to confirm your enrollment. Further information about your start date and next steps will be shared with you shortly.</p><p>Welcome aboard!</p></div>',
            ],
            [
                'name' => 'Monthly Newsletter',
                'subject' => 'This month at our school',
                'category' => 'Newsletter',
                'body_html' => '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#1f2937"><h1 style="color:#0f274a">School newsletter</h1><p>Hello {{name}},</p><h2>Latest news</h2><p>Add this month’s main school update here.</p><h2>Upcoming dates</h2><p>Add important events and deadlines here.</p><p>Thank you for being part of our community.</p><p><a href="{{unsubscribe_url}}" style="color:#64748b;font-size:12px">Unsubscribe</a></p></div>',
            ],
        ];

        foreach (DB::table('organizations')->pluck('id') as $organizationId) {
            foreach ($templates as $template) {
                $exists = DB::table('em_templates')
                    ->where('organization_id', $organizationId)
                    ->where('name', $template['name'])
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $exists) {
                    DB::table('em_templates')->insert([
                        ...$template,
                        'organization_id' => $organizationId,
                        'body_text' => trim(strip_tags(str_replace(['</p>', '</h1>', '</h2>', '<br>'], "\n", $template['body_html']))),
                        'is_active' => true,
                        'created_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Preserve templates because administrators may have customized them.
    }
};
