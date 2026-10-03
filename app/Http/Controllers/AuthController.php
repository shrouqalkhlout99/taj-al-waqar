<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'أدخلي البريد الإلكتروني.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'password.required' => 'أدخلي كلمة المرور.',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'بيانات الدخول غير صحيحة.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function editPassword(): View
    {
        return view('settings.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'أدخلي كلمة المرور الحالية.',
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'password.required' => 'أدخلي كلمة المرور الجديدة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'password.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.',
        ]);

        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('settings.index', ['tab' => 'account'])
            ->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}
