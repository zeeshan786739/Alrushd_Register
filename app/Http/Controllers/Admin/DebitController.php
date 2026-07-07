<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Debit;
use Illuminate\Http\Request;

class DebitController extends Controller
{
      public function __construct()
    {
        $this->middleware('permission:view debit_form')->only('index');
        $this->middleware('permission:create debit_form')->only(['create', 'store']);
        $this->middleware('permission:edit debit_form')->only(['edit', 'update']);
        $this->middleware('permission:delete debit_form')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $data = Debit::latest()->get();
    //     return view('admin.debit.index', compact('data'));
    // }


     public function index(Request $request)
    {
        // মূল query
        $query = Debit::query();

        // 🔍 Universal Search (job_applied_for, surname, country,)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('surename', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('p_country', 'like', "%$search%")
                ->orWhere('mobile_number', 'like', "%$search%");

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
                    $q->where('debit_date', '>=', $start);
                }
                if ($end) {
                    $q->where('debit_date', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission debit_date
        $data = $query->orderBy('debit_date', 'desc')->paginate(10);

        return view('admin.debit.index',compact('data'));
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
        $data = Debit::findOrFail($id);
        return view('admin.debit.show', compact('data'));
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
        $data = Debit::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been deleted successfully.');
    }
}

