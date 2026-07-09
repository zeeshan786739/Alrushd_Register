<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('forms')
            ->whereNotIn('slug', ['student-admission', 'meeting-form'])
            ->where(function ($query) {
                $query->whereNull('handler')
                    ->orWhere('handler', '!=', 'custom');
            })
            ->update(['handler' => 'dynamic']);

        $forms = DB::table('forms')
            ->where('handler', 'dynamic')
            ->whereNotNull('slug')
            ->get(['id', 'slug', 'success_route']);

        foreach ($forms as $form) {
            $success = ltrim((string) $form->success_route, '/');
            if ($success === '' || ! str_starts_with($success, 'forms/')) {
                DB::table('forms')
                    ->where('id', $form->id)
                    ->update(['success_route' => '/forms/'.$form->slug.'/success']);
            }
        }
    }

    public function down(): void
    {
        // Non-reversible data normalization.
    }
};
