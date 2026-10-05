@props(['editUrl', 'deleteUrl', 'itemName'])

<div x-data="{ deleting: false }" class="flex justify-end gap-2">
    <a href="{{ $editUrl }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487l3.651 3.651M18.75 2.25a2.121 2.121 0 013 3L7.5 19.5 3 21l1.5-4.5L18.75 2.25z"/></svg>
        Edit
    </a>
    <button type="button" @click="deleting = true" class="inline-flex items-center gap-1.5 rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79L18.16 19.673A2.25 2.25 0 0115.916 21H8.084a2.25 2.25 0 01-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0V4.5A2.25 2.25 0 0013.5 2.25h-3A2.25 2.25 0 008.25 4.5v.893"/></svg>
        Delete
    </button>

    <div x-cloak x-show="deleting" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4 backdrop-blur-sm" @keydown.escape.window="deleting = false">
        <div x-show="deleting" x-transition class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl" @click.outside="deleting = false">
            <div class="p-6 text-center"><div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-rose-50 text-rose-600"><svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg></div><h3 class="mt-4 text-lg font-bold text-slate-900">Delete this record?</h3><p class="mt-2 text-sm leading-6 text-slate-500"><span class="font-semibold text-slate-700">{{ $itemName }}</span> will be permanently removed. This action cannot be undone.</p></div>
            <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4"><button type="button" @click="deleting = false" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button><form method="POST" action="{{ $deleteUrl }}">@csrf @method('DELETE')<button class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">Yes, delete</button></form></div>
        </div>
    </div>
</div>
