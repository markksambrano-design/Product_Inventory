<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogger;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\InventoryAlertService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($validated);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($validated, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showVerifyEmail()
    {
        return view('auth.verify-email');
    }

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard'))->with('status', 'Your email address has been verified.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if ($user->two_factor_enabled) {
                $code = (string) random_int(100000, 999999);
                $user->forceFill(['two_factor_code'=>Hash::make($code),'two_factor_expires_at'=>now()->addMinutes(10)])->save();
                $request->session()->put(['two_factor_user_id'=>$user->id,'two_factor_remember'=>$request->boolean('remember')]);
                Mail::raw("Your Inventory login code is {$code}. It expires in 10 minutes.", fn($mail)=>$mail->to($user->email)->subject('Inventory Login Code'));
                Auth::logout();
                return redirect()->route('two-factor.challenge');
            }
            $request->session()->regenerate();
            LoginHistory::create(['user_id'=>Auth::id(),'email'=>$request->email,'successful'=>true,'ip_address'=>$request->ip(),'user_agent'=>$request->userAgent()]);
            InventoryAlertService::syncFor(Auth::user());
            ActivityLogger::log('Login', 'Authentication', 'User logged in successfully.');

            return redirect()->intended(route('dashboard'));
        }

        LoginHistory::create(['user_id'=>User::where('email',$request->email)->value('id'),'email'=>$request->email,'successful'=>false,'ip_address'=>$request->ip(),'user_agent'=>$request->userAgent()]);
        ActivityLogger::log('Failed Login', 'Authentication', 'Failed login attempt for: '.$request->email);
        return back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('Logout', 'Authentication', 'User logged out.');
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showTwoFactor(Request $request)
    {
        abort_unless($request->session()->has('two_factor_user_id'), 403);
        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $validated = $request->validate(['code' => 'required|digits:6']);
        $user = User::findOrFail($request->session()->get('two_factor_user_id'));

        if (! $user->two_factor_expires_at || $user->two_factor_expires_at->isPast() || ! Hash::check($validated['code'], $user->two_factor_code)) {
            return back()->withErrors(['code' => 'Invalid or expired security code.']);
        }

        Auth::login($user, $request->session()->get('two_factor_remember', false));
        $user->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null])->save();
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);
        $request->session()->regenerate();
        LoginHistory::create(['user_id' => $user->id, 'email' => $user->email, 'successful' => true, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
        InventoryAlertService::syncFor($user);
        ActivityLogger::log('Login', 'Authentication', 'User logged in with two-factor authentication.');

        return redirect()->intended(route('dashboard'));
    }
}
