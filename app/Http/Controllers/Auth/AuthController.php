<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->auditService->log(
                'auth.rate_limited',
                "Login rate limit exceeded for {$credentials['email']}",
                null,
                ['email' => $credentials['email']],
                'warning'
            );

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            $this->auditService->log(
                'auth.login_failed',
                "Failed login attempt for email: {$credentials['email']}",
                null,
                ['email' => $credentials['email']],
                'warning'
            );

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our institutional records.',
            ]);
        }

        if (! $user->is_active) {
            $this->auditService->log(
                'auth.deactivated_login_attempt',
                "Deactivated user {$user->email} attempted to sign in.",
                null,
                ['user_id' => $user->id],
                'critical',
                $user
            );

            throw ValidationException::withMessages([
                'email' => 'Your staff account is currently deactivated. Please contact the System Administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $this->auditService->log(
            'auth.login_success',
            "User {$user->name} ({$user->role?->name}) signed in.",
            null,
            ['role' => $user->role?->slug, 'office' => $user->office?->code],
            'info',
            $user
        );

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $this->auditService->log(
                'auth.logout',
                "User {$user->name} signed out.",
                null,
                [],
                'info',
                $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been securely signed out.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->auditService->log(
            'auth.password_changed',
            "User {$user->name} updated their account password.",
            null,
            [],
            'info',
            $user
        );

        return back()->with('success', 'Your account password has been updated.');
    }
}
