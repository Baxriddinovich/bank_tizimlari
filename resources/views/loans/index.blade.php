@extends('layouts.app')
@section('title','Kreditlar')
@section('content')
@php use App\Support\Money; use App\Models\LoanApplication as L; @endphp
<div class="flex items-center justify-between mb-4"><h1 class="text-2xl font-semibold">Kredit arizalarim</h1>
  <a href="{{ route('loans.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm">+ Yangi ariza</a></div>
<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto"><table class="w-full text-sm">
  <thead class="text-left text-slate-500 border-b"><tr><th class="p-3">Sana</th><th class="p-3">Tur</th><th class="p-3">Summa</th><th class="p-3">Muddat</th><th class="p-3">Holat</th><th class="p-3">Izoh</th></tr></thead>
  @forelse($loans as $l)
  <tr class="border-b border-slate-100 last:border-0">
    <td class="p-3">{{ $l->created_at->format('d.m.Y') }}</td><td class="p-3">{{ L::TYPES[$l->type] }}</td>
    <td class="p-3">{{ Money::fmt($l->amount) }}</td><td class="p-3">{{ $l->term_months }} oy</td>
    <td class="p-3"><span class="px-2 py-0.5 rounded-full text-xs {{ ['pending'=>'bg-amber-100 text-amber-800','approved'=>'bg-emerald-100 text-emerald-800','rejected'=>'bg-red-100 text-red-800'][$l->status] }}">{{ L::STATUSES[$l->status] }}</span></td>
    <td class="p-3 text-slate-500">{{ $l->review_note }}</td></tr>
  @empty <tr><td colspan="6" class="p-6 text-center text-slate-400">Arizalar yo'q</td></tr> @endforelse
</table></div>
@endsection
