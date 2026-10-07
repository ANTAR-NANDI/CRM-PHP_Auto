<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Store Positions</h1>
                <p class="mt-1 text-sm text-slate-500">Manage positions, designations, and job roles across stores and outlets.</p>
            </div>
            <a href="{{ route('store-positions.create') }}" class="inline-flex items-center justify-center rounded-md bg-gradient-to-r from-[#4165dd] to-[#4432f2] px-4 py-2.5 text-sm font-semibold text-white shadow">+ Add Position</a>
        </div>
    </x-slot>

    <form class="mb-5 flex max-w-xl gap-2">
        <input name="search" value="{{ $search }}" placeholder="Search position name or store..." class="block w-full rounded-md border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
        <button class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white">Search</button>
        @if($search)
            <a href="{{ route('store-positions.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">Clear</a>
        @endif
    </form>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Position</th>
                        <th class="px-5 py-3.5">Store</th>
                        <th class="px-5 py-3.5 text-center">Employees</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($positions as $position)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $position->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-400 font-mono">{{ $position->code }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-slate-700">{{ $position->store?->name ?? '—' }}</span>
                                @if($position->store?->code)
                                    <span class="block text-xs text-slate-400">{{ $position->store->code }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-700">
                                {{ $position->users_count }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $position->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $position->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('store-positions.edit', $position) }}" class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</a>
                                <form method="POST" action="{{ route('store-positions.destroy', $position) }}" class="ml-2 inline" onsubmit="return confirm('Delete this position?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">No store positions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4">
            {{ $positions->links() }}
        </div>
    </section>
</x-app-layout>
