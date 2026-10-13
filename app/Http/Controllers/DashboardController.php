<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        $accounts = $r->user()->accounts()->orderBy('id')->get();
        $ids = $accounts->pluck('id');
        $recent = Transaction::with(['from', 'to'])
            ->where(fn ($q) => $q->whereIn('from_account_id', $ids)->orWhereIn('to_account_id', $ids))
            ->latest()->limit(8)->get();
        return view('dashboard', compact('accounts', 'recent', 'ids'));
    }

    public function openAccount(Request $r)
    {
        $r->validate(['type' => 'required|in:card,savings']);
        abort_if($r->user()->accounts()->count() >= 5, 422, 'Hisoblar soni 5 tadan oshmasligi kerak.');
        $acc = Account::create(['user_id' => $r->user()->id, 'number' => Account::generateNumber(), 'type' => $r->type]);
        AuditLog::record('account_opened', ['account' => $acc->number]);
        return redirect()->route('dashboard')->with('ok', 'Yangi hisob ochildi.');
    }
}
