<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Order;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $weekAgo = Carbon::now()->subDays(7);

        $users = User::count();
        $students = Student::count();
        $revenue = (float) Order::sum('amount');
        $userIncrease = User::where('created_at', '>=', $weekAgo)->count();
        $studentIncrease = Student::where('created_at', '>=', $weekAgo)->count();
        $revenueThisWeek = (float) Order::where('created_at', '>=', $weekAgo)->sum('amount');

        $forms = Form::query()
            ->withCount('entries')
            ->orderByDesc('entries_count')
            ->orderBy('name')
            ->get();

        $formStats = [
            'total_forms' => $forms->count(),
            'active_forms' => $forms->where('is_active', true)->count(),
            'landing_forms' => $forms->filter(fn (Form $form) => $form->hasPlacement('landing'))->count(),
            'total_submissions' => (int) $forms->sum('entries_count'),
            'submissions_this_week' => FormEntry::where('submitted_at', '>=', $weekAgo)->count(),
            'forms_with_entries' => $forms->where('entries_count', '>', 0)->count(),
        ];

        $topForms = $forms->take(6);

        return view('admin.dashboard', [
            'users' => $users,
            'students' => $students,
            'revenue' => $revenue,
            'userIncrease' => $userIncrease,
            'studentIncrease' => $studentIncrease,
            'revenueThisWeek' => $revenueThisWeek,
            'formStats' => $formStats,
            'topForms' => $topForms,
        ]);
    }
}
