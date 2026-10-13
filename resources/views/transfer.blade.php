@extends('layouts.app')
@section('title',"O'tkazma")
@section('content')
@php use App\Support\Money; @endphp
<div class="max-w-lg mx-auto bg-white rounded-2xl border border-slate-200 p-6">
  <h1 class="text-xl font-semibold mb-4">Pul o'tkazish</h1>
  <form method="post" action="{{ route('transfer.store') }}" class="space-y-4">@csrf
    <div><label class="text-sm">Qaysi hisobdan</label>
      <select name="from_account_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('from_account_id')==$a->id)>{{ $a->masked }} — {{ Money::fmt($a->balance) }}</option>@endforeach
      </select>@error('from_account_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div class="flex gap-4 text-sm">
      <label><input type="radio" name="kind" value="internal" @checked(old('kind','internal')==='internal')> Ichki (bank ichida)</label>
      <label><input type="radio" name="kind" value="external" @checked(old('kind')==='external')> Tashqi (boshqa bank)</label>
    </div>
    <div><label class="text-sm">Qabul qiluvchi hisob/karta (16 xona)</label><input name="target" value="{{ old('target') }}" inputmode="numeric" maxlength="16" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
      @error('target')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="text-sm">Summa (so'm)</label><input name="amount" value="{{ old('amount') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
      @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="text-sm">Izoh</label><input name="description" value="{{ old('description') }}" maxlength="150" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
    <button class="w-full rounded-lg bg-emerald-600 text-white py-2.5 font-medium hover:bg-emerald-700">O'tkazish</button>
  </form>
</div>
@endsection
