<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Item Add</h1><p class="mt-1 text-sm text-slate-500">Add an item with its category, landing cost, and opening stock.</p></div></x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="bg-[#1f7d88] px-5 py-3 text-sm font-semibold text-white">Item Information</div><form method="POST" action="{{ route('products.store') }}" class="p-6">@include('admin.products._form')</form></section>
</x-app-layout>
