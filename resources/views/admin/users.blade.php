@extends('layouts.app')
@section('title','Foydalanuvchilar')
@section('content')
@php use App\Support\Money; @endphp
<div class="flex items-center justify-between mb-4"><h1 class="text-2xl font-semibold">Foydalanuvchilar</h1>
  <form><input name="q" value="{{ request('q') }}" placeholder="Qidirish…" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"></form></div>
<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto"><table class="w-full text-sm">
  <thead class="text-left text-slate-500 border-b"><tr><th class="p-3">Ism</th><th class="p-3">Aloqa</th><th class="p-3">Rol</th><th class="p-3">Hisoblar</th><th class="p-3">Holat</th><th class="p-3"></th></tr></thead>
  @foreach($users as $u)
  <tr class="border-b border-slate-100 last:border-0">
    <td class="p-3 font-medium">{{ $u->name }}</td><td class="p-3 text-slate-500">{{ $u->email }}<br>{{ $u->phone }}</td>
    <td class="p-3"><form method="post" action="{{ route('admin.users.role',$u) }}">@csrf
      <select name="role" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-2 py-1" @disabled($u->id===auth()->id())>
        @foreach(['customer'=>'Mijoz','officer'=>'Kredit mutaxassisi','admin'=>'Admin'] as $k=>$v)<option value="{{ $k }}" @selected($u->role===$k)>{{ $v }}</option>@endforeach</select></form></td>
    <td class="p-3">{{ $u->accounts_count }} · {{ Money::fmt((int)$u->accounts_sum_balance) }}</td>
    <td class="p-3">{!! $u->is_blocked ? '<span class="text-red-600">Bloklangan</span>' : '<span class="text-emerald-700">Faol</span>' !!}</td>
    <td class="p-3 text-right">@if($u->id!==auth()->id())<form method="post" action="{{ route('admin.users.block',$u) }}">@csrf<button class="px-3 py-1 rounded-lg border border-slate-300 hover:bg-slate-100">{{ $u->is_blocked ? 'Blokdan chiqarish' : 'Bloklash' }}</button></form>@endif</td>
  </tr>@endforeach
</table></div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
