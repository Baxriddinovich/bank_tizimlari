<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockedUser
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->is_blocked) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')->withErrors(['email' => 'Hisobingiz bloklangan. Bank bilan bog\'laning.']);
        }
        return $next($request);
    }
}
