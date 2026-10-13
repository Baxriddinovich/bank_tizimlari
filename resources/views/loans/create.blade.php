@extends('layouts.app')
@section('title','Kredit ariza')
@section('content')
@php use App\Models\LoanApplication as L; @endphp
<div class="max-w-lg mx-auto bg-white rounded-2xl border border-slate-200 p-6">
  <h1 class="text-xl font-semibold mb-4">Kredit ariza topshirish</h1>
  <form method="post" action="{{ route('loans.store') }}" class="space-y-4">@csrf
    <div><label class="text-sm">Kredit turi</label><select name="type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">@foreach(L::TYPES as $k=>$v)<option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>@endforeach</select></div>
    <div><label class="text-sm">Summa (so'm)</label><input name="amount" value="{{ old('amount') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">@error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="text-sm">Muddat (oy, 3–240)</label><input name="term_months" type="number" min="3" max="240" value="{{ old('term_months',12) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">@error('term_months')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="text-sm">Oylik daromad (so'm)</label><input name="monthly_income" value="{{ old('monthly_income') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">@error('monthly_income')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="text-sm">Maqsad</label><input name="purpose" value="{{ old('purpose') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
    <button class="w-full rounded-lg bg-emerald-600 text-white py-2.5 font-medium hover:bg-emerald-700">Yuborish</button>
  </form>
</div>
@endsection
