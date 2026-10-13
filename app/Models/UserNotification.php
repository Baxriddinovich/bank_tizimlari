<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'url', 'read_at'];

    protected function casts(): array { return ['read_at' => 'datetime']; }

    public function user() { return $this->belongsTo(User::class); }

    /** Ilova ichidagi xabarnoma yaratadi va SMS yuboradi (SMS shlyuzi ulanguncha logga yoziladi). */
    public static function notify(User $user, string $title, string $body, ?string $url = null): self
    {
        $n = static::create(['user_id' => $user->id, 'title' => $title, 'body' => $body, 'url' => $url]);
        Log::channel('single')->info("SMS to={$user->phone}: {$title} — {$body}");
        return $n;
    }
}
