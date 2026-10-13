<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $r)
    {
        $users = User::withCount('accounts')->withSum('accounts', 'balance')
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'ilike', "%{$r->q}%")->orWhere('email', 'ilike', "%{$r->q}%")->orWhere('phone', 'like', "%{$r->q}%")))
            ->orderBy('id')->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function toggleBlock(Request $r, User $user)
    {
        abort_if($user->id === $r->user()->id, 422, 'O\'zingizni bloklab bo\'lmaydi.');
        $user->update(['is_blocked' => !$user->is_blocked]);
        AuditLog::record($user->is_blocked ? 'user_blocked' : 'user_unblocked', ['target' => $user->id]);
        return back()->with('ok', $user->is_blocked ? 'Foydalanuvchi bloklandi.' : 'Blokdan chiqarildi.');
    }

    public function setRole(Request $r, User $user)
    {
        $data = $r->validate(['role' => 'required|in:customer,officer,admin']);
        abort_if($user->id === $r->user()->id, 422, 'O\'z rolingizni o\'zgartirib bo\'lmaydi.');
        $user->update($data);
        AuditLog::record('role_changed', ['target' => $user->id, 'role' => $data['role']]);
        return back()->with('ok', 'Rol o\'zgartirildi.');
    }

    public function audit()
    {
        return view('admin.audit', ['logs' => AuditLog::with('user')->latest()->paginate(30)]);
    }
}
