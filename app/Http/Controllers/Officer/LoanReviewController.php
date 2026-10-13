<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoanApplication;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class LoanReviewController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->query('status', 'pending');
        $loans = LoanApplication::with('user')
            ->when(in_array($status, array_keys(LoanApplication::STATUSES)), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(15)->withQueryString();
        return view('officer.loans', compact('loans', 'status'));
    }

    public function review(Request $r, LoanApplication $loan)
    {
        $data = $r->validate(['decision' => 'required|in:approved,rejected', 'review_note' => 'nullable|string|max:500']);
        abort_unless($loan->status === 'pending', 422, 'Ariza allaqachon ko\'rib chiqilgan.');
        $loan->update([
            'status' => $data['decision'], 'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $r->user()->id, 'reviewed_at' => now(),
        ]);
        UserNotification::notify($loan->user, 'Kredit ariza: '.LoanApplication::STATUSES[$data['decision']], $data['review_note'] ?: 'Ariza ko\'rib chiqildi.', '/loans');
        AuditLog::record('loan_'.$data['decision'], ['loan' => $loan->id]);
        return back()->with('ok', 'Qaror saqlandi.');
    }
}
