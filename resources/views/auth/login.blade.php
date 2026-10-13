@extends('layouts.app')
@section('title','Kirish')
@section('content')
<div class="max-w-sm mx-auto bg-white rounded-2xl border border-slate-200 p-6">
  <h1 class="text-xl font-semibold mb-4">Tizimga kirish</h1>
  <form method="post" action="{{ route('login') }}" class="space-y-4">@csrf
    <div><label class="text-sm">Elektron pochta</label><input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2">
      @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="text-sm">Parol</label><input name="password" type="password" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
    <button class="w-full rounded-lg bg-emerald-600 text-white py-2.5 font-medium hover:bg-emerald-700">Davom etish</button>
  </form>
  <p class="text-sm text-slate-500 mt-4">Hisobingiz yo'qmi? <a class="text-emerald-700" href="{{ route('register') }}">Ro'yxatdan o'tish</a></p>
</div>
@endsection
