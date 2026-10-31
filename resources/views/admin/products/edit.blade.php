<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Edit Product</h1><p class="mt-1 text-sm text-slate-500">Update product, commission, and inventory details.</p></div></x-slot>
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><form method="POST" action="{{ route('products.update', $product) }}" class="p-6">@method('PUT') @include('admin.products._form')</form></section>
</x-app-layout>
