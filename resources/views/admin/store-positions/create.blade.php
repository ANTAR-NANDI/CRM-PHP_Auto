<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Add Store Position</h1>
            <p class="mt-1 text-sm text-slate-500">Define a new job role or title for a store outlet.</p>
        </div>
    </x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Position Details</div>
        <form method="POST" action="{{ route('store-positions.store') }}" class="p-6">
            @include('admin.store-positions._form')
        </form>
    </section>
</x-app-layout>
