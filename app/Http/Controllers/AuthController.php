<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\ResetPasswordCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle user authentication.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = (bool) $request->input('remember');

        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'))->with('success', __('messages.welcome') . ', ' . Auth::user()->name);
        }

        return back()->withErrors([
            'email' => __('messages.login_failed'),
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email|max:150',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=8b5cf6&color=fff',
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', __('messages.registered_success'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', __('messages.logged_out'));
    }

    /**
     * Show forgot password request form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Generate 6-digit code and send via Mail class.
     */
    public function sendResetCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->firstOrFail();

        // Generate 6-digit verification code
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $user->verification_code = $code;
        $user->verification_code_expires_at = now()->addMinutes(15);
        $user->save();

        // Send Email using Mail class (Gmail SMTP)
        $emailSent = false;
        try {
            Mail::to($user->email)->send(new ResetPasswordCodeMail($code, $user->name));
            $emailSent = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SMTP email ugradyp bolmady: ' . $e->getMessage());
            $emailSent = false;
        }

        // Save email in session for redirect to verify screen
        session(['reset_email' => $email]);

        if ($emailSent) {
            $msg = '📧 Tassyklama kody ' . $email . ' e-poçtaňyza ugradyldy! Poçtaňyzy barlaň (Spam bukjasyny hem).';
        } else {
            // Show code in flash only if SMTP completely fails (offline fallback)
            $msg = "⚠️ Email ugradylyp bilinmedi (internet ýa-da SMTP meselesi). Synag kody: {$code}";
        }

        return redirect()->route('password.reset.show')->with('success', $msg);
    }

    /**
     * Show reset password / code verification form.
     */
    public function showResetPassword(Request $request): View
    {
        $email = session('reset_email', $request->query('email', ''));
        return view('auth.reset-password', compact('email'));
    }

    /**
     * Verify 6-digit code and reset user's password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = $request->input('email');
        $code = $request->input('code');
        $password = $request->input('password');

        $user = User::where('email', $email)
            ->where('verification_code', $code)
            ->where('verification_code_expires_at', '>=', now())
            ->first();

        if (!$user) {
            return back()->withErrors([
                'code' => __('messages.invalid_code'),
            ])->withInput();
        }

        // Update password and clear code
        $user->password = Hash::make($password);
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();

        session()->forget('reset_email');

        return redirect()->route('login')->with('success', __('messages.password_reset_success'));
    }
}

