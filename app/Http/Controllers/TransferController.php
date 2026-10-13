<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    public function create(Request $r)
    {
        return view('transfer', ['accounts' => $r->user()->accounts()->orderBy('id')->get()]);
    }

    public function store(Request $r)
    {
        // Probel, tire va boshqa belgilar bilan nusxalangan raqamni qabul qilish
        $r->merge(['target' => preg_replace('/\D/', '', (string) $r->input('target'))]);

        $data = $r->validate([
            'from_account_id' => 'required|integer',
            'kind' => 'required|in:internal,external',
            'target' => ['required', 'regex:/^\d{16}$/'],
            'amount' => 'required|string',
            'description' => 'nullable|string|max:150',
        ], ['target.regex' => 'Hisob/karta raqami 16 xonali bo\'lishi kerak.']);

        $amount = Money::parse($data['amount']);
        if (!$amount || $amount < 100_00) {
            throw ValidationException::withMessages(['amount' => 'Minimal summa 100 so\'m.']);
        }

        $tx = DB::transaction(function () use ($r, $data, $amount) {
            $from = Account::where('id', $data['from_account_id'])->where('user_id', $r->user()->id)->lockForUpdate()->first();
            if (!$from) {
                throw ValidationException::withMessages(['from_account_id' => 'Hisob topilmadi.']);
            }
            if ($from->number === $data['target']) {
                throw ValidationException::withMessages(['target' => 'O\'ziga o\'tkazib bo\'lmaydi.']);
            }
            if ($from->balance < $amount) {
                throw ValidationException::withMessages(['amount' => 'Mablag\' yetarli emas.']);
            }

            $to = Account::where('number', $data['target'])->lockForUpdate()->first();
            if ($data['kind'] === 'internal' && !$to) {
                throw ValidationException::withMessages(['target' => 'Bank ichida bunday hisob topilmadi.']);
            }
            if ($data['kind'] === 'external' && $to) {
                throw ValidationException::withMessages(['target' => 'Bu hisob bankimizga tegishli — "Ichki o\'tkazma"ni tanlang.']);
            }

            $from->decrement('balance', $amount);
            $to?->increment('balance', $amount);

            return Transaction::create([
                'reference' => 'TX'.strtoupper(Str::random(12)),
                'from_account_id' => $from->id,
                'to_account_id' => $to?->id,
                'external_target' => $to ? null : $data['target'],
                'kind' => $data['kind'],
                'amount' => $amount,
                'description' => $data['description'] ?? null,
            ]);
        });

        $tx->load(['from.user', 'to.user']);
        $url = route('transactions.show', $tx, false);
        UserNotification::notify($tx->from->user, 'Pul yechildi', '-'.Money::fmt($tx->amount).' ('.$tx->reference.')', $url);
        if ($tx->to) {
            UserNotification::notify($tx->to->user, 'Pul qabul qilindi', '+'.Money::fmt($tx->amount).' ('.$tx->from->user->name.')', $url);
        }
        AuditLog::record('transfer', ['ref' => $tx->reference, 'amount' => $amount, 'kind' => $tx->kind]);
        return redirect()->route('transactions.show', $tx)->with('ok', 'O\'tkazma muvaffaqiyatli bajarildi.');
    }
}
