@extends('layouts.app')
@section('title',"To'lovlar tarixi")
@section('content')
<h1 class="text-2xl font-semibold mb-4">To'lovlar tarixi</h1>
<form class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 grid sm:grid-cols-3 lg:grid-cols-6 gap-3 text-sm">
  <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <select name="kind" class="rounded-lg border border-slate-300 px-2 py-1.5"><option value="">Barcha turlar</option>
    @foreach(['internal'=>'Ichki','external'=>'Tashqi','deposit'=>"To'ldirish"] as $k=>$l)<option value="{{ $k }}" @selected(request('kind')===$k)>{{ $l }}</option>@endforeach</select>
  <input name="min" placeholder="Min summa" value="{{ request('min') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <input name="max" placeholder="Max summa" value="{{ request('max') }}" class="rounded-lg border border-slate-300 px-2 py-1.5">
  <div class="flex gap-2"><button class="flex-1 rounded-lg bg-emerald-600 text-white px-3 py-1.5">Filtr</button>
    <a href="{{ route('transactions.export', request()->query()) }}" class="rounded-lg border border-slate-300 px-3 py-1.5" title="Excel (CSV)">⬇</a></div>
</form>
@include('partials.tx-table', ['txs' => $txs, 'ids' => $ids])
<div class="mt-4">{{ $txs->links() }}</div>
@endsection
