<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Add Medicine</h1><p class="mt-1 text-sm text-slate-500">Create a medicine before receiving it through a purchase.</p></div></x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Medicine Information</div><form method="POST" action="{{ route('products.store') }}" class="p-6">@include('admin.products._form')</form></section>
</x-app-layout>
