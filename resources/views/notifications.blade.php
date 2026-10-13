@extends('layouts.app')
@section('title','Xabarnomalar')
@section('content')
<div class="flex items-center justify-between mb-4"><h1 class="text-2xl font-semibold">Xabarnomalar</h1>
  <form method="post" action="{{ route('notifications.read') }}">@csrf<button class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100">Hammasini o'qilgan deb belgilash</button></form></div>
<div class="space-y-2">
@forelse($items as $n)
  <a href="{{ $n->url ?: '#' }}" class="block bg-white rounded-xl border p-4 text-sm {{ $n->read_at ? 'border-slate-200' : 'border-emerald-300 bg-emerald-50' }}">
    <div class="flex justify-between"><b>{{ $n->title }}</b><span class="text-slate-400">{{ $n->created_at->diffForHumans() }}</span></div>
    <div class="text-slate-600 mt-1">{{ $n->body }}</div></a>
@empty<div class="text-center text-slate-400 p-8">Xabarnomalar yo'q</div>@endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
