<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister() { return view('auth.register'); }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => ['required', 'regex:/^\+?998\d{9}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'phone.regex' => 'Telefon formati: +998901234567',
            'email.unique' => 'Bu elektron pochta band.',
            'phone.unique' => 'Bu telefon raqami band.',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create($data + ['role' => 'customer']);
            $acc = Account::create(['user_id' => $user->id, 'number' => Account::generateNumber(), 'type' => 'card']);
            if (config('bank.demo')) {
                $acc->update(['balance' => config('bank.demo_bonus')]);
                Transaction::create([
                    'reference' => 'TX'.strtoupper(Str::random(12)), 'to_account_id' => $acc->id,
                    'kind' => 'deposit', 'amount' => config('bank.demo_bonus'), 'description' => 'Boshlang\'ich bonus (demo)',
                ]);
            }
            return $user;
        });

        AuditLog::record('register', [], $user->id);
        return $this->startOtp($r, $user);
    }

    public function showLogin() { return view('auth.login'); }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $r->email)->first();

        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            return back()->withErrors(['email' => 'Ko\'p marta xato urinish. Hisob '.(int) ceil(now()->diffInMinutes($user->locked_until, true)).' daqiqadan keyin ochiladi.'])->onlyInput('email');
        }

        if (!$user || !Hash::check($r->password, $user->password)) {
            if ($user) {
                $attempts = $user->failed_attempts + 1;
                if ($attempts >= config('bank.max_failed')) {
                    $user->forceFill(['locked_until' => now()->addMinutes(config('bank.lock_minutes')), 'failed_attempts' => 0])->save();
                    AuditLog::record('account_locked', [], $user->id);
                } else {
                    $user->forceFill(['failed_attempts' => $attempts])->save();
                }
                AuditLog::record('login_failed', [], $user->id);
            }
            return back()->withErrors(['email' => 'Email yoki parol noto\'g\'ri.'])->onlyInput('email');
        }

        if ($user->is_blocked) {
            return back()->withErrors(['email' => 'Hisobingiz bloklangan. Bank bilan bog\'laning.'])->onlyInput('email');
        }

        $user->update(['failed_attempts' => 0, 'locked_until' => null]);
        return $this->startOtp($r, $user);
    }

    private function startOtp(Request $r, User $user)
    {
        $code = (string) random_int(100000, 999999);
        $r->session()->put('otp', [
            'user_id' => $user->id, 'hash' => Hash::make($code), 'expires' => now()->addMinutes(5)->timestamp, 'tries' => 0,
        ]);
        // SMS shlyuzi ulanmaguncha kod logga yoziladi (va demo rejimda sahifada ko'rsatiladi).
        Log::info("OTP user={$user->id} phone={$user->phone} code={$code}");
        if (config('bank.demo')) {
            $r->session()->flash('demo_otp', $code);
        }
        return redirect()->route('otp');
    }

    public function showOtp(Request $r)
    {
        abort_unless($r->session()->has('otp'), 404);
        return view('auth.otp');
    }

    public function verifyOtp(Request $r)
    {
        $r->validate(['code' => 'required|digits:6']);
        $otp = $r->session()->get('otp');
        if (!$otp || $otp['expires'] < now()->timestamp || $otp['tries'] >= 5) {
            $r->session()->forget('otp');
            return redirect()->route('login')->withErrors(['email' => 'Kod muddati tugagan. Qayta kiring.']);
        }
        if (!Hash::check($r->code, $otp['hash'])) {
            $otp['tries']++;
            $r->session()->put('otp', $otp);
            return back()->withErrors(['code' => 'Kod noto\'g\'ri.']);
        }

        $r->session()->forget('otp');
        Auth::loginUsingId($otp['user_id']);
        $r->session()->regenerate();
        AuditLog::record('login');
        return redirect()->intended(match (Auth::user()->role) {
            'admin' => route('admin.dashboard'),
            'officer' => route('officer.loans'),
            default => route('dashboard'),
        });
    }

    public function logout(Request $r)
    {
        AuditLog::record('logout');
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
