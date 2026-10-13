@extends('layouts.app')
@section('title','Tasdiqlash')
@section('content')
<div class="max-w-sm mx-auto bg-white rounded-2xl border border-slate-200 p-6">
  <h1 class="text-xl font-semibold mb-1">SMS-kodni kiriting</h1>
  <p class="text-sm text-slate-500 mb-4">6 xonali kod 5 daqiqa amal qiladi.</p>
  <form method="post" action="{{ route('otp') }}" class="space-y-4">@csrf
    <input name="code" inputmode="numeric" maxlength="6" autofocus required class="w-full text-center tracking-[0.5em] text-2xl rounded-lg border border-slate-300 px-3 py-2">
    @error('code')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    <button class="w-full rounded-lg bg-emerald-600 text-white py-2.5 font-medium hover:bg-emerald-700">Tasdiqlash</button>
  </form>
</div>
@endsection
