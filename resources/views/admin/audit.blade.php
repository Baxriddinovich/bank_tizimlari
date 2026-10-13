@extends('layouts.app')
@section('title','Audit jurnali')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Audit jurnali</h1>
<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto"><table class="w-full text-sm">
  <thead class="text-left text-slate-500 border-b"><tr><th class="p-3">Vaqt</th><th class="p-3">Foydalanuvchi</th><th class="p-3">Harakat</th><th class="p-3">Ma'lumot</th><th class="p-3">IP</th></tr></thead>
  @foreach($logs as $l)<tr class="border-b border-slate-100 last:border-0"><td class="p-3 whitespace-nowrap">{{ $l->created_at->format('d.m.Y H:i:s') }}</td><td class="p-3">{{ $l->user?->email ?? '—' }}</td><td class="p-3 font-mono">{{ $l->action }}</td><td class="p-3 text-xs text-slate-500">{{ $l->meta ? json_encode($l->meta) : '' }}</td><td class="p-3">{{ $l->ip }}</td></tr>@endforeach
</table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
