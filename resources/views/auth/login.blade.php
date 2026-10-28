<x-guest-layout>
    <div class="-mx-6 -mt-6 mb-8 flex items-center gap-4 rounded-t-3xl bg-gradient-to-r from-emerald-600 to-teal-500 px-8 py-7 sm:-mx-10 sm:-mt-10 sm:px-10">
        <img src="{{ asset('images/php-crm-logo.jpeg') }}" alt="PHP CRM logo" class="h-16 w-16 rounded-full border-2 border-white/60 bg-white object-cover shadow-lg">
        <div><p class="text-sm font-semibold uppercase tracking-widest text-emerald-50">Welcome back</p><h1 class="mt-1 text-3xl font-bold">PHP CRM Login</h1><p class="mt-1 text-emerald-50">Sign in to continue to your automobile dashboard.</p></div>
    </div>
    <x-auth-session-status class="mb-4 text-emerald-200" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="space-y-5">@csrf
        <div><label for="email" class="mb-2 block text-sm font-semibold text-indigo-100">Email address</label><input id="email" class="block w-full rounded-lg border-0 bg-white px-4 py-3 text-slate-900 shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-emerald-300" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"><x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-200" /></div>
        <div><div class="mb-2 flex justify-between"><label for="password" class="text-sm font-semibold text-indigo-100">Password</label>@if (Route::has('password.request'))<a class="text-sm text-emerald-200 hover:text-white" href="{{ route('password.request') }}">Forgot password?</a>@endif</div><input id="password" class="block w-full rounded-lg border-0 bg-white px-4 py-3 text-slate-900 shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-emerald-300" type="password" name="password" required autocomplete="current-password"><x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-200" /></div>
        <label class="flex items-center gap-2 text-sm text-indigo-100"><input type="checkbox" class="rounded border-indigo-300 text-emerald-600 focus:ring-emerald-400" name="remember"> Remember me</label>
        <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 font-bold text-white shadow-lg shadow-emerald-950/40 transition hover:from-emerald-400 hover:to-teal-400">LOGIN TO PHP CRM</button>
    </form>
</x-guest-layout>
