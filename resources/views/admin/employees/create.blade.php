<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Add Employee</h1><p class="mt-1 text-sm text-slate-500">Create a secure employee login and assign their system role.</p></div></x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Employee Information</div><form method="POST" action="{{ route('employees.store') }}" class="p-6">@include('admin.employees._form')</form></section>
</x-app-layout>
