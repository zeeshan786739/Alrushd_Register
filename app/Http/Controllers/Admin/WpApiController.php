<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiApplyNow;
use App\Models\ApiContactForm;
use App\Models\ApiSubmission;
use App\Models\ApiSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WpApiController extends Controller
{


    // <option value="15647">Alrushd Madrasah Course Form</option>
    // <option value="14891">Subscribe</option>
    // <option value="14889">Referral</option>
    // <option value="14881">Enquire Now</option>
    // <option value="14433">Direct Debit Form</option>
    // <option value="14242">Apply Now</option></select>
    // $response = Http::get('https://alrushd.co.uk/wp-json/custom/v1/form-submissions/f16e557');

    // if ($response->successful()) {
    //     $data = collect($response->json()['submissions'])->values();
    //     return view('admin.api.apply-now', compact('data'));
    // }

    // return abort(500, 'API Fetch Failed');


    // public function applyNow()
    // {
    //     $data = ApiApplyNow::where('form_id', 14242)->latest()->get();
    //     // dd($data);
    //     return view('admin.wpapi.apply-now', compact('data'));
    // }

     public function applyNow(Request $request)
    {

        // $query = ApiApplyNow::query();
        $query = ApiApplyNow::where('form_id', '14242');

        // 🔍 Universal Search (name, email, address, country, date)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name_24', 'like', "%$search%")
                ->orWhere('email_1', 'like', "%$search%")
                ->orWhere('phone_2', 'like', "%$search%")

                // 🔍 Date search (যদি search এ তারিখ টাইপ করা হয়)
                ->orWhere('date_created', 'like', "%$search%");
            });
        }

        // 📅 Start & End Date Filtering (date_created based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date_created', '>=', $start);
                }
                if ($end) {
                    $q->where('date_created', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date_created', 'desc')->get();

        return view('admin.wpapi.apply-now', compact('data'));
    }


    // public function onlineMadrasah()
    // {
    //     $data = ApiApplyNow::where('form_id', 15647)->latest()->get();
    //     // dd($data);
    //     return view('admin.wpapi.online-madrasah', compact('data'));
    // }


     public function onlineMadrasah(Request $request)
    {

        // $query = ApiApplyNow::query();
        $query = ApiApplyNow::where('form_id', '15647');

        // 🔍 Universal Search (name, email, address, country, date)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name_24', 'like', "%$search%")
                ->orWhere('email_1', 'like', "%$search%")
                ->orWhere('phone_2', 'like', "%$search%")

                // 🔍 Date search (যদি search এ তারিখ টাইপ করা হয়)
                ->orWhere('date_created', 'like', "%$search%");
            });
        }

        // 📅 Start & End Date Filtering (date_created based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date_created', '>=', $start);
                }
                if ($end) {
                    $q->where('date_created', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date_created', 'desc')->get();

        return view('admin.wpapi.online-madrasah', compact('data'));
    }


    public function studentAdmissionView($entry_id)
    {
        $data = ApiApplyNow::where('entry_id', $entry_id)->first();
        $data['status'] = '1';
        $data->save();
        return view('admin.wpapi.student-admission-view', compact('data'));
    }

    // Job Application
    // public function jobApplications()
    // {
    //     $data = ApiSubmission::where('element_id', '83be6e8')->latest()->get();
    //     return view('admin.wpapi.job-applications', compact('data'));
    // }

   public function jobApplications(Request $request)
{
    $query = ApiSubmission::where('element_id', '83be6e8');

    // Search by name/email/country
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('field_a579d1c', 'like', "%$search%") // name
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('field_e85f923', 'like', "%$search%")
              ->orWhere('field_a8c4c14', 'like', "%$search%");
        });
    }

    // 🧠 Date Filtering (more accurate)
    if ($request->filled('start_date') || $request->filled('end_date')) {
        $start = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : null;

        $end = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : null;

        $query->where(function ($q) use ($start, $end) {
            if ($start) {
                $q->whereRaw("
                    STR_TO_DATE(REPLACE(form_created_at, ',', ''), '%M %d %Y %h:%i %p') >= ?
                ", [$start]);
            }
            if ($end) {
                $q->whereRaw("
                    STR_TO_DATE(REPLACE(form_created_at, ',', ''), '%M %d %Y %h:%i %p') <= ?
                ", [$end]);
            }
        });
    }

    $data = $query->orderByRaw("STR_TO_DATE(REPLACE(form_created_at, ',', ''), '%M %d %Y %h:%i %p') DESC")->get();

    return view('admin.wpapi.job-applications', compact('data'));
}




    public function jobApplicationView($entry_id)
    {
        $data = ApiSubmission::where('entry_id', $entry_id)->first();
        $data['status'] = '1';
        $data->save();
        return view('admin.wpapi.job-applications-view', compact('data'));
    }


    // public function subscribeApplications()
    // {
    //     $data = ApiSubscription::where('form_id', 14891)->latest()->get();
    //     // dd($data);
    //     return view('admin.wpapi.subscription', compact('data'));
    // }

      public function subscribeApplications(Request $request)
    {

        //  $query = ApiSubscription::query();

         $query = ApiSubscription::where('form_id', '14891');

        // 🔍 Universal Search (name, email, address, country, date)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name_2', 'like', "%$search%")
                ->orWhere('phone_1', 'like', "%$search%")
                ->orWhere('email_2', 'like', "%$search%")

                // 🔍 Date search (যদি search এ তারিখ টাইপ করা হয়)
                ->orWhere('date_created', 'like', "%$search%");
            });
        }

        // 📅 Start & End Date Filtering (date_created based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date_created', '>=', $start);
                }
                if ($end) {
                    $q->where('date_created', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date_created', 'desc')->get();
       
        return view('admin.wpapi.subscription', compact('data'));
    }



    // public function enquireNow()
    // {
    //     $data = ApiContactForm::where('form_id', 14881)->latest()->get();
       
    //     return view('admin.wpapi.enquire-now', compact('data'));
    // }


    public function enquireNow(Request $request)
    {
        // $query = ApiContactForm::query();
         $query = ApiContactForm::where('form_id', '14881');

        // 🔍 Universal Search (name, email, address, country, date)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name_2', 'like', "%$search%")
                ->orWhere('name_4', 'like', "%$search%")
                ->orWhere('phone_2', 'like', "%$search%")
                ->orWhere('email_2', 'like', "%$search%")
                ->orWhere('address_7', 'like', "%$search%")

                // 🔍 Date search (যদি search এ তারিখ টাইপ করা হয়)
                ->orWhere('date_created', 'like', "%$search%");
            });
        }

        // 📅 Start & End Date Filtering (date_created based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date_created', '>=', $start);
                }
                if ($end) {
                    $q->where('date_created', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date_created', 'desc')->get();

        return view('admin.wpapi.enquire-now', compact('data'));
    }





    public function enquirenowView($entry_id)
    {
        $data = ApiContactForm::where('entry_id', $entry_id)->first();
        $data['status'] = '1';
        $data->save();
        return view('admin.wpapi.enquire-now-view', compact('data'));
    }

    // public function referralApplications()
    // {
    //     $data = ApiContactForm::where('form_id', 14889)->latest()->get();
    //     return view('admin.wpapi.referal-now', compact('data'));
    // }
    public function referralApplications(Request $request)
    {
        // $query = ApiContactForm::query();
        $query = ApiContactForm::where('form_id', '14889');

        // 🔍 Universal Search (name, email, address, country, date)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name_2', 'like', "%$search%")
                ->orWhere('name_4', 'like', "%$search%")
                ->orWhere('phone_2', 'like', "%$search%")
                ->orWhere('email_2', 'like', "%$search%")
                ->orWhere('address_7', 'like', "%$search%")

                // 🔍 Date search (যদি search এ তারিখ টাইপ করা হয়)
                ->orWhere('date_created', 'like', "%$search%");
            });
        }

        // 📅 Start & End Date Filtering (date_created based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date_created', '>=', $start);
                }
                if ($end) {
                    $q->where('date_created', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date_created', 'desc')->get();

        return view('admin.wpapi.referal-now', compact('data'));
    }

    public function referralnowView($entry_id)
    {
        $data = ApiContactForm::where('entry_id', $entry_id)->first();
         $data['status'] = '1';
        $data->save();
        return view('admin.wpapi.referral-now-view', compact('data'));
    }

    // public function directDebit()
    // {
    //     $data = ApiContactForm::where('form_id', 14433)->latest()->get();
    //     // dd($data);
    //     return view('admin.wpapi.direct-debit', compact('data'));
    // }


    public function directDebit(Request $request)
    {
        // $query = ApiContactForm::query();
        $query = ApiContactForm::where('form_id', '14433');

        // 🔍 Universal Search (name, email, address, country, date)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name_2', 'like', "%$search%")
                ->orWhere('name_4', 'like', "%$search%")
                ->orWhere('phone_2', 'like', "%$search%")
                ->orWhere('email_2', 'like', "%$search%")
                ->orWhere('address_7', 'like', "%$search%")

                // 🔍 Date search (যদি search এ তারিখ টাইপ করা হয়)
                ->orWhere('date_created', 'like', "%$search%");
            });
        }

        // 📅 Start & End Date Filtering (date_created based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('date_created', '>=', $start);
                }
                if ($end) {
                    $q->where('date_created', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('date_created', 'desc')->get();

         return view('admin.wpapi.direct-debit', compact('data'));
    }

    public function debitnowView($entry_id)
    {
        $data = ApiContactForm::where('entry_id', $entry_id)->first();
        $data['status']=1;
        $data->save();
        return view('admin.wpapi.debit-now-view', compact('data'));
    }



    // public function import($form_id)
    // {
    //     // API থেকে ডেটা ফেচ করা
    //     $response = Http::get("https://alrushd.co.uk/wp-json/forminator/v1/all-entries/{$form_id}");

    //     if ($response->failed()) {
    //         return back()->with('error', 'API request failed');
    //     }

    //     $entries = $response->json()['entries'] ?? [];
    //     $unserialize = fn($v) => @unserialize($v) ?: null;

    //     foreach ($entries as $entry) {

    //         // Duplicate check
    //         if (ApiApplyNow::where('entry_id', $entry['entry_id'])->exists()) {
    //             continue;
    //         }

    //         $meta = $entry['meta'] ?? [];

    //         // Upload fields
    //         $uploadFields = [
    //             'upload_1',
    //             'upload_5',
    //             'upload_8',
    //             'upload_11',
    //             'upload_12',
    //             'upload_13',
    //             'upload_16',
    //             'upload_17',
    //             'upload_20',
    //             'upload_21'
    //         ];

    //         $uploadFiles = [];
    //         foreach ($uploadFields as $field) {
    //             $metaKey = str_replace('_', '-', $field); // API থেকে আসে dash সহ
    //             $fileData = optional($unserialize($meta[$metaKey] ?? null))['file']['file_url'] ?? null;
    //             $uploadFiles[$field] = $this->saveRemoteFile($fileData);
    //         }

    //         // Address fields
    //         $addressFields = ['address_1', 'address_2', 'address_3', 'address_4', 'address_5'];
    //         $addresses = [];
    //         foreach ($addressFields as $field) {
    //             $metaKey = str_replace('_', '-', $field);
    //             $addresses[$field] = json_encode($unserialize($meta[$metaKey] ?? ''));
    //         }

    //         // Create DB record
    //         ApiApplyNow::create([
    //             'entry_id' => $entry['entry_id'],
    //             'form_id' => $entry['form_id'],
    //             'is_spam' => $entry['is_spam'] ?? 0,
    //             'date_created' => $entry['date_created'] ?? null,

    //             // Normal fields
    //             'select_10' => $meta['select-10'] ?? null,
    //             'name_2' => $meta['name-2'] ?? null,
    //             'name_3' => $meta['name-3'] ?? null,
    //             'name_4' => $meta['name-4'] ?? null,
    //             'select_1' => $meta['select-1'] ?? null,
    //             'date_1' => $meta['date-1'] ?? null,
    //             'radio_1' => $meta['radio-1'] ?? null,
    //             'radio_2' => $meta['radio-2'] ?? null,
    //             'name_21' => $meta['name-21'] ?? null,
    //             'name_22' => $meta['name-22'] ?? null,
    //             'name_20' => $meta['name-20'] ?? null,
    //             'select_5' => $meta['select-5'] ?? null,
    //             'date_2' => $meta['date-2'] ?? null,
    //             'radio_3' => $meta['radio-3'] ?? null,
    //             'radio_14' => $meta['radio-14'] ?? null,
    //             'name_31' => $meta['name-31'] ?? null,
    //             'name_32' => $meta['name-32'] ?? null,
    //             'name_33' => $meta['name-33'] ?? null,
    //             'select_9' => $meta['select-9'] ?? null,
    //             'date_3' => $meta['date-3'] ?? null,
    //             'radio_13' => $meta['radio-13'] ?? null,
    //             'select_2' => $meta['select-2'] ?? null,
    //             'name_24' => $meta['name-24'] ?? null,
    //             'name_25' => $meta['name-25'] ?? null,
    //             'select_3' => $meta['select-3'] ?? null,
    //             'email_1' => $meta['email-1'] ?? null,
    //             'email_2' => $meta['email-2'] ?? null,
    //             'phone_1' => $meta['phone-1'] ?? null,
    //             'phone_2' => $meta['phone-2'] ?? null,
    //             'name_15' => $meta['name-15'] ?? null,
    //             'radio_5' => $meta['radio-5'] ?? null,
    //             'select_7' => $meta['select-7'] ?? null,
    //             'name_28' => $meta['name-28'] ?? null,
    //             'name_29' => $meta['name-29'] ?? null,
    //             'select_8' => $meta['select-8'] ?? null,
    //             'radio_6' => $meta['radio-6'] ?? null,
    //             'email_5' => $meta['email-5'] ?? null,
    //             'email_6' => $meta['email-6'] ?? null,
    //             'phone_3' => $meta['phone-3'] ?? null,
    //             'phone_4' => $meta['phone-4'] ?? null,
    //             'phone_5' => $meta['phone-5'] ?? null,
    //             'radio_7' => $meta['radio-7'] ?? null,
    //             'radio_8' => $meta['radio-8'] ?? null,
    //             'radio_9' => $meta['radio-9'] ?? null,
    //             'radio_10' => $meta['radio-10'] ?? null,
    //             'radio_11' => $meta['radio-11'] ?? null,
    //             'radio_12' => $meta['radio-12'] ?? null,
    //             'consent_1' => isset($meta['consent-1']) ? 1 : 0,
    //             'text_1' => $meta['text-1'] ?? null,
    //             '_forminator_user_ip' => $meta['_forminator_user_ip'] ?? null,

    //             // Merge address and upload
    //             ...$addresses,
    //             ...$uploadFiles,
    //         ]);
    //     }

    //     return back()->with('success', 'Form entries imported successfully.');
    // }

    // /**
    //  * Remote file save to storage
    //  */
    // private function saveRemoteFile($url)
    // {
    //     if (!$url) return null;

    //     if (is_array($url)) {
    //         $url = reset($url);
    //     }

    //     try {
    //         $contents = @file_get_contents($url);
    //         if (!$contents) return null;

    //         $path = 'form_uploads/' . basename(parse_url($url, PHP_URL_PATH));
    //         Storage::disk('public')->put($path, $contents);
    //         return 'storage/' . $path;
    //     } catch (\Exception $e) {
    //         return null;
    //     }
    // }

    public function import($form_id)
    {
        // Execution time increase
        set_time_limit(300);

        // API থেকে ডেটা ফেচ করা
        $response = Http::get("https://alrushd.co.uk/wp-json/forminator/v1/all-entries/{$form_id}");

        if ($response->failed()) {
            return back()->with('error', 'API request failed');
        }

        $entries = $response->json()['entries'] ?? [];

        foreach ($entries as $entry) {

            // Duplicate check
            if (ApiApplyNow::where('entry_id', $entry['entry_id'])->exists()) {
                continue;
            }

            $meta = $entry['meta'] ?? [];

            // Upload fields
            $uploadFields = [
                'upload_1',
                'upload_5',
                'upload_8',
                'upload_11',
                'upload_12',
                'upload_13',
                'upload_16',
                'upload_17',
                'upload_20',
                'upload_21'
            ];

            $uploadFiles = [];
            foreach ($uploadFields as $field) {
                $metaKey = str_replace('_', '-', $field);
                $data = $meta[$metaKey] ?? null;

                // unserialize only if string
                if (is_string($data)) {
                    $data = @unserialize($data) ?: null;
                }

                // file_url array তৈরি
                $fileUrl = $data['file']['file_url'] ?? null;
                $uploadFiles[$field] = $fileUrl ? [$fileUrl] : [];
            }

            // Address fields
            $addressFields = ['address_1', 'address_2', 'address_3', 'address_4', 'address_5'];
            $addresses = [];
            foreach ($addressFields as $field) {
                $metaKey = str_replace('_', '-', $field);
                $data = $meta[$metaKey] ?? [];

                if (is_string($data)) {
                    $data = @unserialize($data) ?: [];
                }

                $addresses[$field] = (array) $data;
            }

            // DB record create
            ApiApplyNow::create([
                'entry_id' => $entry['entry_id'],
                'form_id' => $entry['form_id'],
                'is_spam' => $entry['is_spam'] ?? 0,
                'date_created' => $entry['date_created'] ?? null,

                // Normal fields
                'select_10' => $meta['select-10'] ?? null,
                'name_2' => $meta['name-2'] ?? null,
                'name_3' => $meta['name-3'] ?? null,
                'name_4' => $meta['name-4'] ?? null,
                'select_1' => $meta['select-1'] ?? null,
                'date_1' => $meta['date-1'] ?? null,
                'radio_1' => $meta['radio-1'] ?? null,
                'radio_2' => $meta['radio-2'] ?? null,
                'name_21' => $meta['name-21'] ?? null,
                'name_22' => $meta['name-22'] ?? null,
                'name_20' => $meta['name-20'] ?? null,
                'select_5' => $meta['select-5'] ?? null,
                'date_2' => $meta['date-2'] ?? null,
                'radio_3' => $meta['radio-3'] ?? null,
                'radio_14' => $meta['radio-14'] ?? null,
                'name_31' => $meta['name-31'] ?? null,
                'name_32' => $meta['name-32'] ?? null,
                'name_33' => $meta['name-33'] ?? null,
                'select_9' => $meta['select-9'] ?? null,
                'date_3' => $meta['date-3'] ?? null,
                'radio_13' => $meta['radio-13'] ?? null,
                'select_2' => $meta['select-2'] ?? null,
                'name_24' => $meta['name-24'] ?? null,
                'name_25' => $meta['name-25'] ?? null,
                'select_3' => $meta['select-3'] ?? null,
                'email_1' => $meta['email-1'] ?? null,
                'email_2' => $meta['email-2'] ?? null,
                'phone_1' => $meta['phone-1'] ?? null,
                'phone_2' => $meta['phone-2'] ?? null,
                'name_15' => $meta['name-15'] ?? null,
                'radio_5' => $meta['radio-5'] ?? null,
                'select_7' => $meta['select-7'] ?? null,
                'name_28' => $meta['name-28'] ?? null,
                'name_29' => $meta['name-29'] ?? null,
                'select_8' => $meta['select-8'] ?? null,
                'radio_6' => $meta['radio-6'] ?? null,
                'email_5' => $meta['email-5'] ?? null,
                'email_6' => $meta['email-6'] ?? null,
                'phone_3' => $meta['phone-3'] ?? null,
                'phone_4' => $meta['phone-4'] ?? null,
                'phone_5' => $meta['phone-5'] ?? null,
                'radio_7' => $meta['radio-7'] ?? null,
                'radio_8' => $meta['radio-8'] ?? null,
                'radio_9' => $meta['radio-9'] ?? null,
                'radio_10' => $meta['radio-10'] ?? null,
                'radio_11' => $meta['radio-11'] ?? null,
                'radio_12' => $meta['radio-12'] ?? null,
                'consent_1' => isset($meta['consent-1']) ? 1 : 0,
                'text_1' => $meta['text-1'] ?? null,
                '_forminator_user_ip' => $meta['_forminator_user_ip'] ?? null,

                // Merge addresses and upload links
                ...$addresses,
                ...$uploadFiles,
            ]);
        }

        return back()->with('success', 'Form entries imported successfully (array for files & addresses).');
    }



    public function importSubscription($form_id)
    {
        // Execution time increase
        set_time_limit(300);

        // API থেকে ডেটা ফেচ করা
        $response = Http::get("https://alrushd.co.uk/wp-json/forminator/v1/all-entries/{$form_id}");

        if ($response->failed()) {
            return back()->with('error', 'API request failed');
        }

        $entries = $response->json()['entries'] ?? [];

        foreach ($entries as $entry) {

            // Duplicate check
            if (ApiSubscription::where('entry_id', $entry['entry_id'])->exists()) {
                continue;
            }

            $meta = $entry['meta'] ?? [];

            // DB record create using Eloquent
            ApiSubscription::create([
                'entry_id' => $entry['entry_id'],
                'form_id' => $entry['form_id'],
                'is_spam' => $entry['is_spam'] ?? 0,
                'date_created' => $entry['date_created'] ?? null,
                'name_2' => $meta['name-2'] ?? null,
                'phone_1' => $meta['phone-1'] ?? null,
                'email_2' => $meta['email-2'] ?? null,
                'select_3' => $meta['select-3'] ?? null,
                '_forminator_user_ip' => $meta['_forminator_user_ip'] ?? null,
            ]);
        }

        return back()->with('success', 'Subscription entries imported successfully.');
    }

    public function importContact($form_id)
    {
        // সময়সীমা বাড়ানো (optional)
        set_time_limit(300);

        // API থেকে ডেটা ফেচ
        $response = Http::get("https://alrushd.co.uk/wp-json/forminator/v1/all-entries/{$form_id}");

        if ($response->failed()) {
            return back()->with('error', 'API request failed');
        }

        $entries = $response->json()['entries'] ?? [];

        foreach ($entries as $entry) {

            // Duplicate চেক
            if (ApiContactForm::where('entry_id', $entry['entry_id'])->exists()) {
                continue;
            }

            $meta = $entry['meta'] ?? [];

            // JSON ফিল্ডগুলোর জন্য প্রি-প্রসেসিং
            $jsonFields = ['date_1', 'date_2', 'address_6', 'address_7', 'address_8'];
            $jsonData = [];

            foreach ($jsonFields as $field) {
                $metaKey = str_replace('_', '-', $field);
                $data = $meta[$metaKey] ?? null;

                if (is_string($data)) {
                    $data = @unserialize($data) ?: null;
                }

                $jsonData[$field] = is_array($data) ? $data : ($data ? [$data] : []);
            }

            // ডেটা Create
            ApiContactForm::create([
                'entry_id' => $entry['entry_id'],
                'form_id' => $entry['form_id'],
                'is_spam' => $entry['is_spam'] ?? 0,
                'date_created' => $entry['date_created'] ?? null,

                'name_2' => $meta['name-2'] ?? null,
                'name_4' => $meta['name-4'] ?? null,
                'name_30' => $meta['name-30'] ?? null,
                'name_31' => $meta['name-31'] ?? null,
                'name_32' => $meta['name-32'] ?? null,
                'name_33' => $meta['name-33'] ?? null,
                'name_34' => $meta['name-34'] ?? null,
                'name_35' => $meta['name-35'] ?? null,
                'name_36' => $meta['name-36'] ?? null,
                'name_37' => $meta['name-37'] ?? null,
                'name_38' => $meta['name-38'] ?? null,
                'name_39' => $meta['name-39'] ?? null,
                'phone_2' => $meta['phone-2'] ?? null,
                'email_1' => $meta['email-1'] ?? null,
                'email_2' => $meta['email-2'] ?? null,
                'select_3' => $meta['select-3'] ?? null,
                'checkbox_1' => $meta['checkbox-1'] ?? null,
                'textarea_1' => $meta['textarea-1'] ?? null,
                'radio_2' => $meta['radio-2'] ?? null,
                'select_9' => $meta['select-9'] ?? null,
                'select_10' => $meta['select-10'] ?? null,
                'select_11' => $meta['select-11'] ?? null,
                'consent_1' => $meta['consent-1'] ?? null,
                '_forminator_user_ip' => $meta['_forminator_user_ip'] ?? null,

                // Merge JSON Fields
                ...$jsonData,
            ]);
        }

        return back()->with('success', 'Contact form entries imported successfully.');
    }

    public function importJobApplication($form_id)
    {

        // dd($form_id);
        // Execution time increase
        set_time_limit(300);

        // API থেকে ডেটা ফেচ করা
        $response = Http::get("https://alrushd.co.uk/wp-json/custom/v1/form-submissions/{$form_id}");

        if ($response->failed()) {
            return back()->with('error', 'API request failed.');
        }

        // মূল submissions data নেওয়া
        $data = $response->json();
        $entries = $data['submissions'] ?? [];

        if (empty($entries)) {
            return back()->with('error', 'No entries found in API response.');
        }

        foreach ($entries as $entry) {
            // Duplicate check
            if (ApiSubmission::where('entry_id', $entry['id'] ?? null)->exists()) {
                continue;
            }

            ApiSubmission::create([
                'element_id'      => $form_id ?? null,
                'entry_id'        => $entry['id'] ?? null,
                'form_created_at' => $entry['created_at'] ?? null,
                'name'            => $entry['name'] ?? null,
                'field_a579d1c'   => $entry['field_a579d1c'] ?? null,
                'email'           => $entry['email'] ?? null,
                'field_c90264c'   => $entry['field_c90264c'] ?? null,
                'message'         => $entry['message'] ?? null,
                'field_fa1fc8d'   => $entry['field_fa1fc8d'] ?? null,
                'field_36f8b56'   => $entry['field_36f8b56'] ?? null,
                'field_facd0f4'   => $entry['field_facd0f4'] ?? null,
                'field_6ccfce5'   => $entry['field_6ccfce5'] ?? null,
                'field_926b28e'   => $entry['field_926b28e'] ?? null,
                'field_e85f923'   => $entry['field_e85f923'] ?? null,
                'field_47d2c6a'   => $entry['field_47d2c6a'] ?? null,
                'field_0bc35f1'   => $entry['field_0bc35f1'] ?? null,
                'field_047fc60'   => $entry['field_047fc60'] ?? null,
                'field_a068999'   => $entry['field_a068999'] ?? null,
                'field_e95ba4d'   => $entry['field_e95ba4d'] ?? null,
                'field_b2dcf7c'   => $entry['field_b2dcf7c'] ?? null,
                'field_a8c4c14'   => $entry['field_a8c4c14'] ?? null,
                'field_dae410a'   => $entry['field_dae410a'] ?? null,
                'field_f2a4f37'   => $entry['field_f2a4f37'] ?? null,
                'field_0064921'   => $entry['field_0064921'] ?? null,
                'field_28fb8c2'   => $entry['field_28fb8c2'] ?? null,
            ]);
        }

        return back()->with('success', 'Job Application entries imported successfully.');
    }


    public function fornView($entry_id)
    {
        $submission = ApiApplyNow::where('entry_id', $entry_id)->first();
        return view('admin.wpapi.form-view', compact('submission'));
    }
}


