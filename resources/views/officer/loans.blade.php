@extends('layouts.app')
@section('title','Kredit arizalar')
@section('content')
@php use App\Support\Money; use App\Models\LoanApplication as L; @endphp
<h1 class="text-2xl font-semibold mb-4">Kredit arizalar</h1>
<div class="flex gap-2 mb-4 text-sm">@foreach(L::STATUSES as $k=>$v)<a href="?status={{ $k }}" class="px-3 py-1.5 rounded-lg border {{ $status===$k ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 bg-white' }}">{{ $v }}</a>@endforeach</div>
<div class="space-y-3">
@forelse($loans as $l)
  <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm">
    <div class="flex flex-wrap justify-between gap-2"><div><b>{{ $l->user->name }}</b> <span class="text-slate-500">{{ $l->user->phone }}</span></div><div class="text-slate-500">{{ $l->created_at->format('d.m.Y H:i') }}</div></div>
    <div class="mt-2 grid sm:grid-cols-4 gap-2"><div>{{ L::TYPES[$l->type] }}</div><div><b>{{ Money::fmt($l->amount) }}</b></div><div>{{ $l->term_months }} oy</div><div>Daromad: {{ Money::fmt($l->monthly_income ?? 0) }}</div></div>
    @if($l->purpose)<div class="text-slate-500 mt-1">Maqsad: {{ $l->purpose }}</div>@endif
    @if($l->status==='pending')
    <form method="post" action="{{ route('officer.loans.review',$l) }}" class="mt-3 flex flex-wrap gap-2">@csrf
      <input name="review_note" placeholder="Izoh" class="flex-1 min-w-48 rounded-lg border border-slate-300 px-3 py-1.5">
      <button name="decision" value="approved" class="px-4 py-1.5 rounded-lg bg-emerald-600 text-white">Tasdiqlash</button>
      <button name="decision" value="rejected" class="px-4 py-1.5 rounded-lg bg-red-600 text-white">Rad etish</button>
    </form>
    @else<div class="mt-2 text-slate-500">{{ L::STATUSES[$l->status] }}: {{ $l->review_note }}</div>@endif
  </div>
@empty<div class="text-center text-slate-400 p-8">Arizalar yo'q</div>@endforelse
</div>
<div class="mt-4">{{ $loans->links() }}</div>
@endsection
