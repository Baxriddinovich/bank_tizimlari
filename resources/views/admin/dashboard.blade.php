@extends('layouts.app')
@section('title','Admin')
@section('content')
@php use App\Support\Money; use App\Models\LoanApplication as L; @endphp
<h1 class="text-2xl font-semibold mb-4">Boshqaruv paneli</h1>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  @foreach($stats as $label => $val)
  <div class="bg-white rounded-2xl border border-slate-200 p-4"><div class="text-xs text-slate-500">{{ $label }}</div><div class="text-xl font-semibold mt-1">{{ $val }}</div></div>
  @endforeach
</div>
<div class="grid lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-4">
    <h2 class="font-semibold mb-3">So'nggi 7 kun: o'tkazmalar hajmi</h2>
    <div class="flex items-end gap-2 h-40">
      @foreach($days as $d)
      <div class="flex-1 flex flex-col items-center justify-end h-full">
        <div class="text-[10px] text-slate-500 mb-1">{{ $d['count'] }}</div>
        <div class="w-full bg-emerald-500 rounded-t" style="height: {{ max(2, round($d['sum'] / $max * 100)) }}%" title="{{ Money::fmt($d['sum']) }}"></div>
        <div class="text-[11px] text-slate-500 mt-1">{{ $d['label'] }}</div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <h2 class="font-semibold mb-3">Kutilayotgan arizalar</h2>
    @forelse($pending as $l)
      <div class="text-sm border-b border-slate-100 py-2 last:border-0"><b>{{ $l->user->name }}</b><br><span class="text-slate-500">{{ L::TYPES[$l->type] }} · {{ Money::fmt($l->amount) }}</span></div>
    @empty<div class="text-sm text-slate-400">Yo'q</div>@endforelse
    <a href="{{ route('officer.loans') }}" class="text-sm text-emerald-700 mt-2 inline-block">Ko'rib chiqish →</a>
  </div>
</div>
<div class="bg-white rounded-2xl border border-slate-200 p-4 mt-4">
  <h2 class="font-semibold mb-3">Yangi foydalanuvchilar</h2>
  @foreach($newUsers as $u)<div class="text-sm flex justify-between border-b border-slate-100 py-2 last:border-0"><span>{{ $u->name }} <span class="text-slate-400">{{ $u->email }}</span></span><span class="text-slate-500">{{ $u->created_at->diffForHumans() }}</span></div>@endforeach
</div>
@endsection
