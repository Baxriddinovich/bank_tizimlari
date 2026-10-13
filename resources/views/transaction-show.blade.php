@extends('layouts.app')
@section('title','Amal')
@section('content')
@php use App\Support\Money; @endphp
<div class="max-w-lg mx-auto bg-white rounded-2xl border border-slate-200 p-6 text-sm space-y-2">
  <h1 class="text-xl font-semibold mb-2">Amal tafsilotlari</h1>
  <div class="text-3xl font-semibold mb-4">{{ Money::fmt($t->amount) }}</div>
  @foreach([
    'Referens' => $t->reference, 'Sana' => $t->created_at->format('d.m.Y H:i:s'),
    'Tur' => ['internal'=>'Ichki','external'=>'Tashqi','deposit'=>"To'ldirish"][$t->kind],
    'Holat' => $t->status === 'completed' ? 'Bajarildi' : $t->status,
    'Qayerdan' => $t->from ? $t->from->formatted_number.' ('.$t->from->user->name.')' : '—',
    'Qayerga' => $t->to ? $t->to->formatted_number.' ('.$t->to->user->name.')' : ($t->external_target ?? '—'),
    'Izoh' => $t->description ?: '—'] as $k => $v)
  <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><span class="text-slate-500">{{ $k }}</span><span class="text-right font-medium">{{ $v }}</span></div>
  @endforeach
  <div class="pt-3 flex gap-3"><button onclick="window.print()" class="px-4 py-2 rounded-lg border border-slate-300">Chop etish / PDF</button><a href="{{ route('transactions') }}" class="px-4 py-2 text-emerald-700">← Tarix</a></div>
</div>
@endsection
