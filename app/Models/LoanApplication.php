<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    protected $fillable = ['user_id', 'type', 'amount', 'term_months', 'monthly_income', 'purpose', 'status', 'reviewed_by', 'review_note', 'reviewed_at'];

    protected function casts(): array { return ['reviewed_at' => 'datetime']; }

    public const TYPES = ['consumer' => 'Iste\'mol krediti', 'auto' => 'Avtokredit', 'mortgage' => 'Ipoteka', 'education' => 'Ta\'lim krediti'];
    public const STATUSES = ['pending' => 'Ko\'rib chiqilmoqda', 'approved' => 'Tasdiqlandi', 'rejected' => 'Rad etildi'];

    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
