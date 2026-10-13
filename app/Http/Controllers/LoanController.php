<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoanApplication;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $r)
    {
        return view('loans.index', ['loans' => $r->user()->loans()->latest()->get()]);
    }

    public function create() { return view('loans.create'); }

    public function store(Request $r)
    {
        $data = $r->validate([
            'type' => 'required|in:'.implode(',', array_keys(LoanApplication::TYPES)),
            'amount' => 'required|string',
            'term_months' => 'required|integer|min:3|max:240',
            'monthly_income' => 'required|string',
            'purpose' => 'nullable|string|max:200',
        ]);
        $amount = Money::parse($data['amount']);
        $income = Money::parse($data['monthly_income']);
        if (!$amount || $amount < 1_000_000_00 || $amount > 5_000_000_000_00) {
            throw ValidationException::withMessages(['amount' => 'Summa 1 000 000 dan 5 000 000 000 so\'mgacha bo\'lishi kerak.']);
        }
        if (!$income) {
            throw ValidationException::withMessages(['monthly_income' => 'Oylik daromadni to\'g\'ri kiriting.']);
        }
        if ($r->user()->loans()->where('status', 'pending')->count() >= 3) {
            throw ValidationException::withMessages(['amount' => 'Ko\'rib chiqilayotgan 3 tadan ortiq ariza bo\'lishi mumkin emas.']);
        }

        $loan = $r->user()->loans()->create([
            'type' => $data['type'], 'amount' => $amount, 'term_months' => $data['term_months'],
            'monthly_income' => $income, 'purpose' => $data['purpose'] ?? null,
        ]);
        AuditLog::record('loan_applied', ['loan' => $loan->id, 'amount' => $amount]);
        return redirect()->route('loans.index')->with('ok', 'Kredit ariza topshirildi.');
    }
}
