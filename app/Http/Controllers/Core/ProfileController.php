<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the user profile settings page.
     */
    public function index(): View
    {
        $user = Auth::user()->load([
            'roles',
            'employee.department',
            'employee.position',
            'intern.department',
            'intern.mentor',
        ]);

        return view('profile.index', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's basic profile details.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        if ($user->employee && isset($validated['phone'])) {
            $user->employee->update([
                'phone' => $validated['phone'],
            ]);
        }

        return redirect()->route('profile.index')->with('success', 'Profil Anda telah berhasil diperbarui.');
    }

    /**
     * Update the user's account password securely.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.index')->with('success', 'Kata sandi berhasil diubah secara aman.');
    }
}
