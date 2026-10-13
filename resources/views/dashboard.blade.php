@extends('layouts.app')
@section('title','Hisoblar')
@section('content')
@php use App\Support\Money; @endphp
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-semibold">Mening hisoblarim</h1>
  <form method="post" action="{{ route('accounts.open') }}" class="flex gap-2">@csrf
    <select name="type" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"><option value="card">Karta</option><option value="savings">Jamg'arma</option></select>
    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm hover:bg-slate-100">+ Hisob ochish</button>
  </form>
</div>
<div class="grid md:grid-cols-3 gap-4 mb-8">
  @foreach($accounts as $a)
  <div class="rounded-2xl p-5 text-white bg-gradient-to-br {{ $a->type==='card' ? 'from-emerald-600 to-teal-700' : 'from-indigo-600 to-slate-800' }}">
    <div class="text-xs uppercase opacity-80">{{ $a->type==='card' ? 'Karta' : "Jamg'arma" }}</div>
    <div class="text-2xl font-semibold mt-2">{{ Money::fmt($a->balance, $a->currency) }}</div>
    <div class="flex items-center justify-between mt-4">
      <div class="font-mono text-sm opacity-90">{{ $a->formatted_number }}</div>
      <button type="button" data-copy="{{ $a->formatted_number }}" class="copy-btn text-xs px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">Nusxalash</button>
    </div>
  </div>
  @endforeach
</div>
<h2 class="font-semibold mb-2">So'nggi amallar</h2>
@include('partials.tx-table', ['txs' => $recent, 'ids' => $ids])
<a href="{{ route('transactions') }}" class="text-sm text-emerald-700 mt-2 inline-block">Barchasini ko'rish →</a>
<script>
document.querySelectorAll('.copy-btn').forEach(function (b) {
  b.addEventListener('click', function () {
    var t = b.dataset.copy;
    var done = function () { b.textContent = 'Nusxalandi ✓'; setTimeout(function () { b.textContent = 'Nusxalash'; }, 1500); };
    if (navigator.clipboard) { navigator.clipboard.writeText(t).then(done); }
    else { var i = document.createElement('input'); i.value = t; document.body.appendChild(i); i.select(); document.execCommand('copy'); i.remove(); done(); }
  });
});
</script>
@endsection
