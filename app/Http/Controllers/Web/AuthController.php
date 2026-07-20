<?php

namespace App\Http\Controllers\Web;

use App\Enums\LoginEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\LoginLockoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LoginLockoutService $lockoutService,
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $email = $credentials['email'];

        if ($this->lockoutService->tooManyAttempts($email)) {
            $seconds = $this->lockoutService->availableIn($email);

            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ])->onlyInput('email');
        }

        $user = User::where('email', $email)->first();

        if ($user && $user->suspended_at) {
            $this->authService->logAudit($user->id, $credentials['email'], LoginEvent::LoginSuspended->value, $request);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->put('_login_ip', $request->ip());
            $this->lockoutService->clear($email);
            $this->authService->logAudit(Auth::id(), $credentials['email'], LoginEvent::LoginSuccess->value, $request);

            return redirect()->intended(route('dashboard'));
        }

        $this->lockoutService->hit($request, $email);
        $this->authService->logAudit(null, $credentials['email'], LoginEvent::LoginFailed->value, $request);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logAudit(Auth::id(), Auth::user()->email, LoginEvent::Logout->value, $request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegistrationForm(): View
    {
        abort_unless(config('app.allow_registration', false), 403);

        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        abort_unless(config('app.allow_registration', false), 403, 'Registration is disabled.');

        $validated = $request->validated();

        $this->authService->register($validated);

        return redirect()->route('login')->with('success', 'Account created successfully.');
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return back()->with('success', 'If that email exists in our system, a password reset link has been sent.');
    }

    public function showResetForm(string $token): View
    {
        return view('auth.reset-password', compact('token'));
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $password = $request->validated('password');

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($password) {
                $this->authService->logPasswordReset($user, $password);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function profile(): View
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->checkOptimisticLock($user, $request);

        $validated = $request->validated();

        $this->authService->updateProfile($user, $validated);

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }
}
