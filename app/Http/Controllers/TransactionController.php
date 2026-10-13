<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\Money;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private function query(Request $r)
    {
        $ids = $r->user()->accounts()->pluck('id');
        $q = Transaction::with(['from', 'to'])
            ->where(fn ($q) => $q->whereIn('from_account_id', $ids)->orWhereIn('to_account_id', $ids));

        if ($r->filled('from')) $q->whereDate('created_at', '>=', $r->from);
        if ($r->filled('to')) $q->whereDate('created_at', '<=', $r->to);
        if ($r->filled('kind') && in_array($r->kind, ['internal', 'external', 'deposit'])) $q->where('kind', $r->kind);
        if ($r->filled('min') && ($m = Money::parse($r->min)) !== null) $q->where('amount', '>=', $m);
        if ($r->filled('max') && ($m = Money::parse($r->max)) !== null) $q->where('amount', '<=', $m);

        return [$q->latest(), $ids];
    }

    public function index(Request $r)
    {
        [$q, $ids] = $this->query($r);
        $txs = $q->paginate(15)->withQueryString();
        return view('transactions', compact('txs', 'ids'));
    }

    public function export(Request $r)
    {
        [$q, $ids] = $this->query($r);
        $rows = $q->limit(5000)->get();
        return response()->streamDownload(function () use ($rows, $ids) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Sana', 'Referens', 'Tur', 'Qayerdan', 'Qayerga', 'Summa (so\'m)', 'Izoh'], ';');
            foreach ($rows as $t) {
                $sign = $ids->contains($t->from_account_id) ? -1 : 1;
                fputcsv($out, [
                    $t->created_at->format('Y-m-d H:i'), $t->reference, $t->kind,
                    $t->from?->number, $t->to?->number ?? $t->external_target,
                    number_format($sign * $t->amount / 100, 2, ',', ''), $t->description,
                ], ';');
            }
            fclose($out);
        }, 'tolovlar-tarixi-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(Request $r, Transaction $transaction)
    {
        $ids = $r->user()->accounts()->pluck('id');
        abort_unless($ids->contains($transaction->from_account_id) || $ids->contains($transaction->to_account_id), 403);
        $transaction->load(['from.user', 'to.user']);
        return view('transaction-show', ['t' => $transaction, 'ids' => $ids]);
    }
}
