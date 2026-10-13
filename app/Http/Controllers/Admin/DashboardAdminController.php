<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\LoanApplication;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $transfers = Transaction::whereIn('kind', ['internal', 'external']);

        $stats = [
            'Mijozlar' => User::where('role', 'customer')->count(),
            'Bloklanganlar' => User::where('is_blocked', true)->count(),
            'Hisoblar' => Account::count(),
            'Jami balans' => Money::fmt((int) Account::sum('balance')),
            'Bugungi o\'tkazmalar' => (clone $transfers)->where('created_at', '>=', $today)->count(),
            'Bugungi hajm' => Money::fmt((int) (clone $transfers)->where('created_at', '>=', $today)->sum('amount')),
            'Kutilayotgan arizalar' => LoanApplication::where('status', 'pending')->count(),
            'Tasdiqlangan kreditlar' => Money::fmt((int) LoanApplication::where('status', 'approved')->sum('amount')),
        ];

        // Oxirgi 7 kun bo'yicha o'tkazmalar hajmi
        $raw = (clone $transfers)->where('created_at', '>=', now()->subDays(6)->startOfDay())->get(['amount', 'created_at'])
            ->groupBy(fn ($t) => $t->created_at->format('Y-m-d'));
        $days = collect(range(6, 0))->map(function ($i) use ($raw) {
            $d = now()->subDays($i)->format('Y-m-d');
            $rows = $raw->get($d, collect());
            return ['label' => substr($d, 5), 'count' => $rows->count(), 'sum' => (int) $rows->sum('amount')];
        });
        $max = max(1, $days->max('sum'));

        $newUsers = User::latest()->limit(5)->get();
        $pending = LoanApplication::with('user')->where('status', 'pending')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'days', 'max', 'newUsers', 'pending'));
    }

    public function transactions(Request $r)
    {
        $q = Transaction::with(['from.user', 'to.user'])->latest();
        if ($r->filled('from')) $q->whereDate('created_at', '>=', $r->from);
        if ($r->filled('to')) $q->whereDate('created_at', '<=', $r->to);
        if ($r->filled('kind') && in_array($r->kind, ['internal', 'external', 'deposit'])) $q->where('kind', $r->kind);
        if ($r->filled('q')) {
            $term = trim($r->q);
            $q->where(fn ($w) => $w->where('reference', 'like', "%{$term}%")
                ->orWhere('external_target', 'like', "%{$term}%")
                ->orWhereHas('from', fn ($a) => $a->where('number', 'like', "%{$term}%"))
                ->orWhereHas('to', fn ($a) => $a->where('number', 'like', "%{$term}%")));
        }
        if ($r->filled('min') && ($m = Money::parse($r->min)) !== null) $q->where('amount', '>=', $m);
        if ($r->filled('max') && ($m = Money::parse($r->max)) !== null) $q->where('amount', '<=', $m);

        return view('admin.transactions', ['txs' => $q->paginate(25)->withQueryString()]);
    }
}
