<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->suspended_at) {
            $this->authService->logAudit($user->id, $credentials['email'], 'login_suspended', $request);

            return back()->withErrors([
                'email' => 'Your account has been suspended.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->authService->logAudit(Auth::id(), $credentials['email'], 'login_success', $request);

            return redirect()->intended(route('dashboard'));
        }

        $this->authService->logAudit(null, $credentials['email'], 'login_failed', $request);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logAudit(Auth::id(), Auth::user()->email, 'logout', $request);
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

    public function register(Request $request): RedirectResponse
    {
        abort_unless(config('app.allow_registration', false), 403, 'Registration is disabled.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
        ]);

        $this->authService->register($validated);

        return redirect()->route('login')->with('success', 'Account created successfully. Please check your email to verify your account.');
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

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) {
                $this->authService->logPasswordReset($user);
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

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->checkOptimisticLock($user, $request);

        $validated = $request->validate([
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
            'current_password' => 'required_with:password|string|current_password',
        ]);

        $this->authService->updateProfile($user, $validated);

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        $result = $this->authService->verifyEmail($user, $hash);

        return match ($result) {
            'invalid_link' => redirect()->route('dashboard')->with('error', 'Invalid verification link.'),
            'already_verified' => redirect()->route('dashboard')->with('info', 'Email already verified.'),
            default => redirect()->route('dashboard')->with('success', 'Email verified successfully.'),
        };
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('info', 'Email already verified.');
        }

        $user->sendEmailVerificationNotification();

        return redirect()->back()->with('success', 'Verification email sent.');
    }
}
