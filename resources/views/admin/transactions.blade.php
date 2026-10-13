@extends('layouts.app')
@section('title',"Barcha o'tkazmalar")
@section('content')
@php use App\Support\Money; @endphp
<h1 class="text-2xl font-semibold mb-4">Barcha o'tkazmalar</h1>
<form class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 grid sm:grid-cols-3 lg:grid-cols-7 gap-3 text-sm">
  <input name="q" value="{{ request('q') }}" placeholder="Referens / hisob" class="lg:col-span-2 rounded-lg border border-slate-300 px-2 py-1.5">
  <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <select name="kind" class="rounded-lg border border-slate-300 px-2 py-1.5"><option value="">Barcha turlar</option>
    @foreach(['internal'=>'Ichki','external'=>'Tashqi','deposit'=>"To'ldirish"] as $k=>$l)<option value="{{ $k }}" @selected(request('kind')===$k)>{{ $l }}</option>@endforeach</select>
  <input name="min" placeholder="Min" value="{{ request('min') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <input name="max" placeholder="Max" value="{{ request('max') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <button class="rounded-lg bg-emerald-600 text-white px-3 py-1.5 sm:col-span-3 lg:col-span-7">Filtr</button>
</form>
<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto"><table class="w-full text-sm">
  <thead class="text-left text-slate-500 border-b"><tr><th class="p-3">Sana</th><th class="p-3">Referens</th><th class="p-3">Tur</th><th class="p-3">Kimdan</th><th class="p-3">Kimga</th><th class="p-3 text-right">Summa</th></tr></thead>
  @forelse($txs as $t)
  <tr class="border-b border-slate-100 last:border-0">
    <td class="p-3 whitespace-nowrap">{{ $t->created_at->format('d.m.Y H:i') }}</td>
    <td class="p-3 font-mono text-xs">{{ $t->reference }}</td>
    <td class="p-3">{{ ['internal'=>'Ichki','external'=>'Tashqi','deposit'=>"To'ldirish"][$t->kind] }}</td>
    <td class="p-3">{{ $t->from?->user->name ?? '—' }}<div class="font-mono text-xs text-slate-400">{{ $t->from?->number }}</div></td>
    <td class="p-3">{{ $t->to?->user->name ?? 'Tashqi' }}<div class="font-mono text-xs text-slate-400">{{ $t->to?->number ?? $t->external_target }}</div></td>
    <td class="p-3 text-right font-medium whitespace-nowrap">{{ Money::fmt($t->amount) }}</td>
  </tr>
  @empty<tr><td colspan="6" class="p-6 text-center text-slate-400">Topilmadi</td></tr>@endforelse
</table></div>
<div class="mt-4">{{ $txs->links() }}</div>
@endsection
