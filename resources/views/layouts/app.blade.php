<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Pharmacy Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
</head>
<body class="bg-[#f3f5fb] font-sans text-slate-800 antialiased">
<div x-data="{ mobileNavOpen: false }" @keydown.escape.window="mobileNavOpen = false" class="min-h-screen print:pl-0 lg:pl-64">
    @include('layouts.navigation')
    <div x-cloak x-show="mobileNavOpen" x-transition.opacity @click="mobileNavOpen = false" class="fixed inset-0 z-20 bg-slate-950/55 backdrop-blur-[1px] lg:hidden print:hidden" aria-hidden="true"></div>
    <div class="flex min-h-screen flex-col">
        <header class="h-16 shrink-0 bg-[#203D9F] text-white shadow-sm print:hidden">
            <div class="mx-auto flex h-full w-full max-w-[1500px] items-center justify-between px-5 lg:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="mobileNavOpen = true" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-white/20 text-xl hover:bg-white/10 lg:hidden" aria-label="Open navigation menu" :aria-expanded="mobileNavOpen.toString()">☰</button>
                    <div class="min-w-0"><p class="truncate text-sm font-semibold">Pharmacy Management</p><p class="hidden text-xs text-indigo-300 sm:block">Secure admin workspace</p></div>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10"><span class="grid h-8 w-8 place-items-center rounded-full bg-indigo-500 text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="hidden text-sm font-semibold sm:block">{{ auth()->user()->name }}</span></a>
            </div>
        </header>
        @isset($header)<div class="border-b border-slate-200 bg-white print:hidden"><div class="mx-auto w-full max-w-[1500px] px-5 py-5 lg:px-6">{{ $header }}</div></div>@endisset
        <main class="flex-1"><div class="mx-auto w-full max-w-[1500px] px-5 py-6 lg:px-6 lg:py-8">
            @if(session('success'))<div x-data="{ show: true, init() { setTimeout(() => this.show = false, 2000) } }" x-show="show" x-transition.opacity.duration.200ms class="mb-5 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 print:hidden"><span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-500 text-xs text-white">✓</span>{{ session('success') }}</div>@endif
            @if(session('error'))<div x-data="{ show: true, init() { setTimeout(() => this.show = false, 2000) } }" x-show="show" x-transition.opacity.duration.200ms class="mb-5 flex items-center gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 print:hidden"><span class="grid h-6 w-6 place-items-center rounded-full bg-rose-500 text-xs text-white">!</span>{{ session('error') }}</div>@endif
            {{ $slot }}
        </div></main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.js-data-table').each(function () {
            if (!$.fn.dataTable.isDataTable(this)) {
                $(this).DataTable({ pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [] });
            }
        });
    });
</script>
</body>
</html>
