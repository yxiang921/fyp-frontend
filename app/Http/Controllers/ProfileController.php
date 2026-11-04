<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'agency' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Handle avatar upload to Cloudinary
        if ($request->hasFile('avatar')) {
            $result = cloudinary()->uploadApi()->upload($request->file('avatar')->getRealPath(), [
                'folder' => 'findsight/avatars',
                'transformation' => [
                    'width' => 256,
                    'height' => 256,
                    'crop' => 'fill',
                    'gravity' => 'face',
                ],
            ]);
            $data['avatar'] = $result['secure_url'];
        }

        $user->update($data);

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function security()
    {
        return view('profile.security');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => $data['password']]);

        return redirect()->route('profile.security')->with('success', 'Password changed successfully.');
    }
}
