<!doctype html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Realbank') — Realbank</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
@php $u = auth()->user(); $unread = $u && $u->role==='customer' ? \App\Models\UserNotification::where('user_id',$u->id)->whereNull('read_at')->count() : 0; @endphp
<header class="bg-white border-b border-slate-200">
  <div class="max-w-6xl mx-auto px-4 h-14 flex items-center gap-6">
    <a href="{{ route('home') }}" class="font-bold text-lg text-emerald-700">Real<span class="text-slate-800">bank</span></a>
    @auth
    <nav class="flex gap-4 text-sm overflow-x-auto">
      @if($u->role === 'customer')
        <a class="hover:text-emerald-700" href="{{ route('dashboard') }}">Hisoblar</a>
        <a class="hover:text-emerald-700" href="{{ route('transfer') }}">O'tkazma</a>
        <a class="hover:text-emerald-700" href="{{ route('transactions') }}">Tarix</a>
        <a class="hover:text-emerald-700" href="{{ route('loans.index') }}">Kredit</a>
      @endif
      @if($u->isRole('officer','admin'))<a class="hover:text-emerald-700" href="{{ route('officer.loans') }}">Kredit arizalar</a>@endif
      @if($u->role === 'admin')
        <a class="hover:text-emerald-700" href="{{ route('admin.dashboard') }}">Bosh sahifa</a>
        <a class="hover:text-emerald-700" href="{{ route('admin.users') }}">Foydalanuvchilar</a>
        <a class="hover:text-emerald-700" href="{{ route('admin.transactions') }}">O'tkazmalar</a>
        <a class="hover:text-emerald-700" href="{{ route('admin.audit') }}">Audit</a>
      @endif
    </nav>
    <div class="ml-auto flex items-center gap-3 text-sm">
      @if($u->role==='customer')<a href="{{ route('notifications') }}" class="relative px-2" title="Xabarnomalar">🔔@if($unread)<span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] rounded-full px-1.5">{{ $unread }}</span>@endif</a>@endif
      <span class="hidden sm:inline text-slate-500">{{ $u->name }} · {{ ['customer'=>'Mijoz','officer'=>'Kredit mutaxassisi','admin'=>'Admin'][$u->role] }}</span>
      <form method="post" action="{{ route('logout') }}">@csrf<button class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100">Chiqish</button></form>
    </div>
    @else
    <div class="ml-auto flex gap-2 text-sm">
      <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg hover:bg-slate-100">Kirish</a>
      <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Ro'yxatdan o'tish</a>
    </div>
    @endauth
  </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6">
  @if(session('ok'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('ok') }}</div>@endif
  @if(session('demo_otp'))<div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">Demo rejim: SMS-kod <b class="font-mono text-base">{{ session('demo_otp') }}</b></div>@endif
  @yield('content')
</main>
</body>
</html>
