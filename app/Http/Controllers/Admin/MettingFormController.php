<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Metting;
use Illuminate\Http\Request;

class MettingFormController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:view metting_form')->only('index');
        $this->middleware('permission:create metting_form')->only(['create', 'store']);
        $this->middleware('permission:edit metting_form')->only(['edit', 'update']);
        $this->middleware('permission:delete metting_form')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $data = Metting::latest()->get();
    //     return view('admin.metting.index',compact('data'));
    // }

     public function index(Request $request)
    {
        // মূল query
        $query = Metting::query();

        // 🔍 Universal Search (job_applied_for, surname, country,)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");

            });
        }

        // 📅 Start & End Date Filtering (date_of_submission based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date', '>=', $start);
                }
                if ($end) {
                    $q->where('date', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date', 'desc')->paginate(10);

        return view('admin.metting.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Metting::findOrFail($id);
        return view('admin.metting.show',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Metting::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been deleted successfully.');
    }
}

