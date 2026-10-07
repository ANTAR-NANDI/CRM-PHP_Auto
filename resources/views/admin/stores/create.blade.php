<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Add Store</h1>
            <p class="mt-1 text-sm text-slate-500">Create a new showroom, warehouse, or retail store outlet.</p>
        </div>
    </x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Store Information</div>
        <form method="POST" action="{{ route('stores.store') }}" class="p-6">
            @include('admin.stores._form')
        </form>
    </section>
</x-app-layout>
