<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['reference', 'from_account_id', 'to_account_id', 'external_target', 'kind', 'amount', 'description', 'status'];

    public function from() { return $this->belongsTo(Account::class, 'from_account_id'); }
    public function to() { return $this->belongsTo(Account::class, 'to_account_id'); }
}
