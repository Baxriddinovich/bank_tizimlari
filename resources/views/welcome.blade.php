@extends('layouts.app')
@section('content')
<div class="py-16 text-center max-w-2xl mx-auto">
  <h1 class="text-4xl font-bold mb-4">Onlayn bank xizmatlari</h1>
  <p class="text-slate-600 mb-8">Hisob balansi, ichki va tashqi o'tkazmalar, to'lov tarixi va kredit ariza — hammasi bir joyda.</p>
  <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700">Boshlash</a>
  <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl border border-slate-300 ml-2">Kirish</a>
</div>
@endsection
