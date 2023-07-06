<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // dd($request->user());
        $validated = $request->validateWithBag('updatePassword', [
            // 'current_password' => ['required', 'current_password'],
            'current_password' => ['required'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // $validated= $request->validateWithBag('updatePassword', [
        //     'current_password' => ['required'],
        //     'password' => ['required', 'confirmed'],
        // ]);

        $request->user()->update([
            // 'password' => Hash::make($validated['password']),
            'password' => $validated['password'],
        ]);

        return back()->with('status', 'password-updated');
    }
}
