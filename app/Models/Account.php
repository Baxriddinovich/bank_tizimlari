<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['user_id', 'number', 'type', 'currency', 'balance'];

    public function user() { return $this->belongsTo(User::class); }

    public function transactions()
    {
        return Transaction::where('from_account_id', $this->id)->orWhere('to_account_id', $this->id);
    }

    public static function generateNumber(): string
    {
        do {
            $n = '8600'.str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (static::where('number', $n)->exists());
        return $n;
    }

    public function getMaskedAttribute(): string
    {
        return substr($this->number, 0, 4).' **** **** '.substr($this->number, -4);
    }

    public function getFormattedNumberAttribute(): string
    {
        return trim(chunk_split($this->number, 4, ' '));
    }
}
