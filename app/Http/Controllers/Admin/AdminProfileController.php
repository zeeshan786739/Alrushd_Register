<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class AdminProfileController extends Controller
{
    public function settings()
    {
        return view('admin.account.profile.edit');
    }

    public function changePassword()
    {
        return view('admin.account.security.edit');
    }
    public function updateSettings(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
        ]);

        $image = $request->hasFile('image') ? ImageHelper::uploadImage($request->file('image')) : null;

        if ($request->hasFile('image') && $admin->image) {
            Storage::disk('public')->delete($admin->image);
        }

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'image' =>  $image,
        ]);

        return redirect()
            ->route('admin.account.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!\Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match.']);
        }

        $admin->update([
            'password' => \Hash::make($request->new_password),
        ]);

        return redirect()
            ->route('admin.account.security')
            ->with('success', 'Password changed successfully.');
    }
}
