<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'meta', 'ip'];

    protected function casts(): array { return ['meta' => 'array']; }

    public function user() { return $this->belongsTo(User::class); }

    public static function record(string $action, array $meta = [], ?int $userId = null): void
    {
        static::create(['user_id' => $userId ?? auth()->id(), 'action' => $action, 'meta' => $meta, 'ip' => request()->ip()]);
    }
}
