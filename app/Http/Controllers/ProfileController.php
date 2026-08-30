<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // Update profile photo
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete('profiles/' . $user->profile_photo);
        }

        $file = $request->file('profile_photo');
        $filename = 'user_' . $user->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('profiles', $filename, 'public');

        $user->update([
            'profile_photo' => $filename,
        ]);

        return redirect()->route('profile.edit')->with('status', 'photo-updated');
    }

    // Delete profile photo
    public function deletePhoto(Request $request)
    {
        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete('profiles/' . $user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return redirect()->route('profile.edit')->with('status', 'photo-deleted');
    }
}
