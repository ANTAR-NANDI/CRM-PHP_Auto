<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Product Add</h1><p class="mt-1 text-sm text-slate-500">Create a product with sales, commission, and inventory details.</p></div></x-slot>
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><form method="POST" action="{{ route('products.store') }}" class="p-6">@include('admin.products._form')</form></section>
</x-app-layout>
