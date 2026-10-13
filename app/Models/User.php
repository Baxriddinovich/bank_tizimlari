<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'is_blocked'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    public function accounts() { return $this->hasMany(Account::class); }
    public function loans() { return $this->hasMany(LoanApplication::class); }
    public function isRole(string ...$roles): bool { return in_array($this->role, $roles, true); }
}
