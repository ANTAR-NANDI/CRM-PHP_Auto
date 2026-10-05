<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div class="mb-7"><p class="text-sm font-semibold uppercase tracking-widest text-cyan-200">Password recovery</p><h1 class="mt-2 text-2xl font-bold">Choose a new password</h1><p class="mt-2 text-sm text-indigo-100">Your email code was verified. Set a secure new password.</p></div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="mb-2 block text-sm font-semibold text-indigo-100">New password</label>
            <input id="password" class="block w-full rounded-lg border-0 bg-white px-4 py-3 text-slate-900 shadow-sm" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-200" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-indigo-100">Confirm new password</label>
            <input id="password_confirmation" class="block w-full rounded-lg border-0 bg-white px-4 py-3 text-slate-900 shadow-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-rose-200" />
        </div>

        <button class="mt-6 w-full rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 font-bold text-white shadow-lg shadow-indigo-950/40">RESET PASSWORD</button>
    </form>
</x-guest-layout>
