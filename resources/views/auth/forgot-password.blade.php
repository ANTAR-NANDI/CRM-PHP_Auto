<x-guest-layout>
    <div class="mb-7"><p class="text-sm font-semibold uppercase tracking-widest text-cyan-200">Password recovery</p><h1 class="mt-2 text-2xl font-bold">Reset your password</h1><p class="mt-2 text-sm leading-6 text-indigo-100">Enter your login email and we will send a six-digit one-time code.</p></div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-indigo-100">Login email</label>
            <input id="email" class="block w-full rounded-lg border-0 bg-white px-4 py-3 text-slate-900 shadow-sm ring-1 ring-white/20 focus:ring-2 focus:ring-cyan-300" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-200" />
        </div>

        <button class="mt-5 w-full rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 font-bold text-white shadow-lg shadow-indigo-950/40">SEND OTP CODE</button>
        <div class="mt-5 text-center"><a href="{{ route('login') }}" class="text-sm font-semibold text-cyan-200 hover:text-white">Back to sign in</a></div>
    </form>
</x-guest-layout>
