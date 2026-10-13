@php use App\Support\Money; @endphp
<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
<table class="w-full text-sm">
  <thead class="text-left text-slate-500 border-b border-slate-200"><tr><th class="p-3">Sana</th><th class="p-3">Tur</th><th class="p-3">Kimga / Kimdan</th><th class="p-3">Izoh</th><th class="p-3 text-right">Summa</th></tr></thead>
  <tbody>
  @forelse($txs as $t)
    @php $out = $ids->contains($t->from_account_id); @endphp
    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
      <td class="p-3 whitespace-nowrap"><a class="text-emerald-700" href="{{ route('transactions.show',$t) }}">{{ $t->created_at->format('d.m.Y H:i') }}</a></td>
      <td class="p-3">{{ ['internal'=>'Ichki','external'=>'Tashqi','deposit'=>"To'ldirish"][$t->kind] }}</td>
      <td class="p-3 font-mono text-xs">{{ $out ? ($t->to?->number ?? $t->external_target) : ($t->from?->number ?? '—') }}</td>
      <td class="p-3 text-slate-500">{{ $t->description }}</td>
      <td class="p-3 text-right font-medium whitespace-nowrap {{ $out ? 'text-red-600' : 'text-emerald-700' }}">{{ $out ? '−' : '+' }}{{ Money::fmt($t->amount) }}</td>
    </tr>
  @empty
    <tr><td colspan="5" class="p-6 text-center text-slate-400">Amallar yo'q</td></tr>
  @endforelse
  </tbody>
</table></div>
