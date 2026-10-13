@extends('layouts.app')
@section('title',"Ro'yxatdan o'tish")
@section('content')
<div class="max-w-sm mx-auto bg-white rounded-2xl border border-slate-200 p-6">
  <h1 class="text-xl font-semibold mb-4">Ro'yxatdan o'tish</h1>
  <form method="post" action="{{ route('register') }}" class="space-y-4">@csrf
    @foreach([['name','F.I.Sh.','text'],['email','Elektron pochta','email'],['phone','Telefon (+998901234567)','text'],['password','Parol (kamida 8, harf va raqam)','password'],['password_confirmation','Parolni takrorlang','password']] as [$n,$l,$t])
    <div><label class="text-sm">{{ $l }}</label><input name="{{ $n }}" type="{{ $t }}" value="{{ $t==='password' ? '' : old($n) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
      @error($n)<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
    @endforeach
    <button class="w-full rounded-lg bg-emerald-600 text-white py-2.5 font-medium hover:bg-emerald-700">Ro'yxatdan o'tish</button>
  </form>
</div>
@endsection
