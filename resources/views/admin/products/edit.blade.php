<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Edit Medicine</h1><p class="mt-1 text-sm text-slate-500">Update catalog information without changing purchase history.</p></div></x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Medicine Information</div><form method="POST" action="{{ route('products.update', $product) }}" class="p-6">@method('PUT') @include('admin.products._form')</form></section>
</x-app-layout>
