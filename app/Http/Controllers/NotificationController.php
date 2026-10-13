<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $r)
    {
        $items = UserNotification::where('user_id', $r->user()->id)->latest()->paginate(20);
        return view('notifications', compact('items'));
    }

    public function readAll(Request $r)
    {
        UserNotification::where('user_id', $r->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return back();
    }
}
