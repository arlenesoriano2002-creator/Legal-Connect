<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PerTabAuthHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AdminForgotPasswordController extends Controller
{
    private const SESSION_KEY = 'admin_account_password_reset';
    private const CODE_TTL_MINUTES = 10;
    private const RESET_TTL_MINUTES = 15;
    private const MAX_VERIFY_ATTEMPTS = 5;

    public function showEmailForm()
    {
        $user = $this->getAuthenticatedAdmin();

        return view('admin-account-setting.forgotPassword', [
            'user' => $user,
            'mode' => 'email',
            'resetState' => $this->getResetState(),
        ]);
    }

    public function sendCode(Request $request)
    {
        $user = $this->getAuthenticatedAdmin();

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        if (strcasecmp($validated['email'], $user->email) !== 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'The email address must match your current admin account email.',
                ]);
        }

        $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);

        $this->storeResetState([
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make($verificationCode),
            'expires_at' => $expiresAt->toIso8601String(),
            'verified' => false,
            'verified_at' => null,
            'reset_expires_at' => null,
            'verify_attempts' => 0,
        ]);

        if (!$this->sendVerificationEmail($user, $verificationCode, $expiresAt)) {
            $this->clearResetState();

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Unable to send the verification code. Please verify the SMTP settings and try again.',
                ]);
        }

        return redirect()
            ->route('admin.account.settings.forgot-password.verify')
            ->with('success', 'A 6-digit verification code has been sent to your email address.');
    }

    public function showVerifyForm()
    {
        $user = $this->getAuthenticatedAdmin();
        $state = $this->getResetState();

        if (!$this->hasValidStateForUser($user, $state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Please start the forgot password process again.',
                ]);
        }

        if ($this->isCodeExpired($state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Your verification code has expired. Please request a new code.',
                ]);
        }

        return view('admin-account-setting.forgotPassword', [
            'user' => $user,
            'mode' => 'verify',
            'resetState' => $state,
        ]);
    }

    public function verifyCode(Request $request)
    {
        $user = $this->getAuthenticatedAdmin();
        $state = $this->getResetState();

        if (!$this->hasValidStateForUser($user, $state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Please start the forgot password process again.',
                ]);
        }

        $validated = $request->validate([
            'verification_code' => 'required|digits:6',
        ]);

        if ($this->isCodeExpired($state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Your verification code has expired. Please request a new code.',
                ]);
        }

        if (!Hash::check($validated['verification_code'], $state['code_hash'] ?? '')) {
            $state['verify_attempts'] = (int) ($state['verify_attempts'] ?? 0) + 1;

            if ($state['verify_attempts'] >= self::MAX_VERIFY_ATTEMPTS) {
                $this->clearResetState();

                return redirect()
                    ->route('admin.account.settings.forgot-password.email')
                    ->withErrors([
                        'email' => 'Too many invalid verification attempts. Please request a new code.',
                    ]);
            }

            $this->storeResetState($state);

            return back()->withErrors([
                'verification_code' => 'The verification code is invalid.',
            ]);
        }

        $state['verified'] = true;
        $state['verified_at'] = now()->toIso8601String();
        $state['reset_expires_at'] = now()->addMinutes(self::RESET_TTL_MINUTES)->toIso8601String();
        $state['code_hash'] = null;
        $state['verify_attempts'] = 0;

        $this->storeResetState($state);

        return redirect()
            ->route('admin.account.settings.forgot-password.reset')
            ->with('success', 'Verification successful. You can now set a new password.');
    }

    public function showResetForm()
    {
        $user = $this->getAuthenticatedAdmin();
        $state = $this->getResetState();

        if (!$this->hasValidStateForUser($user, $state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Please start the forgot password process again.',
                ]);
        }

        if (empty($state['verified'])) {
            return redirect()
                ->route('admin.account.settings.forgot-password.verify')
                ->withErrors([
                    'verification_code' => 'Please verify your code before resetting your password.',
                ]);
        }

        if ($this->isResetExpired($state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Your password reset session has expired. Please request a new verification code.',
                ]);
        }

        return view('admin-account-setting.forgotPassword', [
            'user' => $user,
            'mode' => 'reset',
            'resetState' => $state,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $user = $this->getAuthenticatedAdmin();
        $state = $this->getResetState();

        if (!$this->hasValidStateForUser($user, $state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Please start the forgot password process again.',
                ]);
        }

        if (empty($state['verified']) || $this->isResetExpired($state)) {
            $this->clearResetState();

            return redirect()
                ->route('admin.account.settings.forgot-password.email')
                ->withErrors([
                    'email' => 'Your password reset session has expired. Please request a new verification code.',
                ]);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        $this->clearResetState();

        return redirect()
            ->route('admin.account.settings')
            ->with('success', 'Your password has been updated successfully.');
    }

    private function getAuthenticatedAdmin(): User
    {
        $user = PerTabAuthHelper::getTabUser();

        if (!$user || !in_array($user->role, ['admin', 'superadmin'], true)) {
            abort(403, 'Unauthorized access.');
        }

        return $user;
    }

    private function getResetState(): ?array
    {
        $state = session(self::SESSION_KEY);

        return is_array($state) ? $state : null;
    }

    private function storeResetState(array $state): void
    {
        session()->put(self::SESSION_KEY, $state);
        session()->save();
    }

    private function clearResetState(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function hasValidStateForUser(User $user, ?array $state): bool
    {
        if (!$state) {
            return false;
        }

        return (int) ($state['user_id'] ?? 0) === (int) $user->id
            && strcasecmp((string) ($state['email'] ?? ''), $user->email) === 0;
    }

    private function isCodeExpired(array $state): bool
    {
        $expiresAt = $state['expires_at'] ?? null;

        return !$expiresAt || now()->greaterThan(Carbon::parse($expiresAt));
    }

    private function isResetExpired(array $state): bool
    {
        $expiresAt = $state['reset_expires_at'] ?? null;

        return !$expiresAt || now()->greaterThan(Carbon::parse($expiresAt));
    }

    private function sendVerificationEmail(User $user, string $verificationCode, Carbon $expiresAt): bool
    {
        $fromAddress = config('mail.from.address') ?: config('mail.mailers.smtp.username');
        $fromName = config('mail.from.name');

        try {
            Mail::mailer(config('mail.default'))->send(
                'emails.admin_password_reset_code',
                [
                    'user' => $user,
                    'verificationCode' => $verificationCode,
                    'expiresAt' => $expiresAt,
                ],
                function ($message) use ($user, $fromAddress, $fromName) {
                    if (!empty($fromAddress)) {
                        $message->from($fromAddress, $fromName);
                    }

                    $message->to($user->email)
                        ->subject('Admin Password Reset Code - Legal Connect');
                }
            );

            Log::info('Admin password reset code email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send admin password reset email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return false;
        }
    }
}
