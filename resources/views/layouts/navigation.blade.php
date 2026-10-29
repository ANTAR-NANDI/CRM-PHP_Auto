<aside :class="{ 'translate-x-0': mobileNavOpen }" class="app-sidebar fixed inset-y-0 left-0 z-30 flex w-72 -translate-x-full flex-col overflow-hidden bg-[#171E2E] text-white shadow-2xl transition-transform duration-300 lg:w-64 lg:translate-x-0 lg:shadow-none">
    <div class="sidebar-brand flex h-20 shrink-0 items-center gap-3 px-5"><img src="{{ asset('images/php-crm-logo.jpeg') }}" alt="PHP CRM" class="h-11 w-11 rounded-full border border-white/20 bg-white object-cover"><div class="min-w-0 flex-1"><p class="truncate text-base font-bold tracking-tight">PHP CRM</p><p class="mt-0.5 text-[10px] font-semibold uppercase tracking-[.16em] text-teal-200/80">Automobile CRM</p></div><button type="button" @click="mobileNavOpen = false" class="grid h-10 w-10 place-items-center rounded-xl text-2xl leading-none text-indigo-100 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-teal-300 lg:hidden" aria-label="Close navigation menu">×</button></div>
    <nav
        x-data
        x-init="$nextTick(() => {
            const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
            const links = Array.from($el.querySelectorAll('a[href]'));
            const matches = links.filter((link) => {
                const targetPath = new URL(link.href).pathname.replace(/\/$/, '') || '/';
                return currentPath === targetPath || (targetPath !== '/' && currentPath.startsWith(`${targetPath}/`));
            });
            const selected = matches.sort((a, b) => b.pathname.length - a.pathname.length)[0];
            if (selected) {
                selected.classList.remove('text-indigo-200', 'text-indigo-100');
                selected.classList.add('bg-gradient-to-r', 'from-[#3f57ed]', 'to-[#4632f5]', 'font-semibold', 'text-white');
                selected.setAttribute('aria-current', 'page');
            }
        })"
        class="sidebar-scroll flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm"
        aria-label="Primary navigation"
    >
        @can('dashboard.view')<a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-[#3f57ed] to-[#4632f5] font-semibold' : 'text-indigo-100 hover:bg-white/10' }}"><span>⌂</span>Dashboard</a>@endcan

        @if(auth()->user()->canAny(['contacts.manage','organizations.manage','leads.manage','activities.manage','todos.manage','performance-reports.view']))
        <div x-data="{ open: {{ request()->is('crm/*') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Customer Relationship</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                @can('activities.manage')<a href="{{ route('activities.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Activity</a>@endcan
                @can('leads.manage')<a href="{{ route('leads.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Leads</a>@endcan
                @can('todos.manage')<a href="{{ route('todos.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">To Do List</a>@endcan
                @can('contacts.manage')<a href="{{ route('contacts.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Contact</a>@endcan
                @can('organizations.manage')<a href="{{ route('organizations.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Organization</a>@endcan
                @can('performance-reports.view')<a href="{{ route('performance.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Performance Report</a>@endcan
            </div>
        </div>
        @endif

        @if(auth()->user()->canAny(['budgets.manage','events.manage','bulk-sms.manage']))
        <div x-data="{ open: {{ request()->is('promotion/*') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Promotion &amp; Campaign</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                @can('events.manage')<a href="{{ route('events.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Events</a>@endcan
                @can('budgets.manage')<a href="{{ route('budgets.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Budgets</a>@endcan
                @can('events.manage')<a href="{{ route('event-activities.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Event Activity</a><a href="{{ route('event-expenses.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Event Expense</a>@endcan
                @can('bulk-sms.manage')<a href="{{ route('bulk-sms.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Bulk SMS Blast</a><a href="{{ route('bulk-sms.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">SMS Send List</a>@endcan
            </div>
        </div>
        @endif

        @if(auth()->user()->canAny(['pre-event-plans.manage','agendas.manage','meetings.manage']))<div x-data="{ open: {{ request()->is('planning/*') ? 'true' : 'false' }} }"><button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Meeting &amp; Planning</span><span :class="open ? 'rotate-90' : ''">›</span></button><div x-cloak x-show="open" class="space-y-0.5 pl-3">@can('pre-event-plans.manage')<a href="{{ route('pre-event-plans.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Pre-event Plans</a>@endcan @can('agendas.manage')<a href="{{ route('agendas.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Agenda List</a>@endcan @can('meetings.manage')<a href="{{ route('meetings.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Meetings</a>@endcan</div></div>@endif

        @include('layouts.navigation-order-sales')

        {{-- Customer Relationship is intentionally shown after Purchase & Receive below, matching the operating flow. --}}
        {{--
        @if(auth()->user()->canAny(['contacts.manage','organizations.manage','leads.manage','activities.manage','todos.manage','performance-reports.view']))
        <div x-data="{ open: {{ request()->is('crm/*') ? 'true' : 'false' }} }"><button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Customer Relationship</span><span :class="open ? 'rotate-90' : ''">›</span></button><div x-cloak x-show="open" class="space-y-0.5 pl-3">@can('contacts.manage')<a href="{{ route('contacts.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Contact Add / Contact</a>@endcan @can('organizations.manage')<a href="{{ route('organizations.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Organization</a>@endcan @can('leads.manage')<a href="{{ route('leads.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Lead Add / Leads</a>@endcan @can('activities.manage')<a href="{{ route('activities.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Activity Add</a>@endcan @can('todos.manage')<a href="{{ route('todos.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">To Do Add</a><a href="{{ route('todos.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">To Do List</a>@endcan @can('performance-reports.view')<a href="{{ route('performance.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Performance Report</a>@endcan</div></div>
        @endif
        --}}
        @if(auth()->user()->canAny(['products.view','products.manage','suppliers.manage','sales.manage','purchases.manage','reports.view']))
        <div x-data="{ open: {{ request()->is('admin/products*') || request()->is('admin/item-categories*') || request()->is('admin/receives*') || request()->is('admin/item-transfers*') || request()->is('sales/issues*') || request()->is('sales/issue-returns*') || request()->is('sales/requisitions*') || request()->is('admin/reports/stock*') ? 'true' : 'false' }} }"><button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Item Inventory</span><span :class="open ? 'rotate-90' : ''">›</span></button><div x-cloak x-show="open" class="space-y-0.5 pl-3">@can('products.manage')<a href="{{ route('item-categories.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Item Category</a><a href="{{ route('products.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Item</a>@endcan @can('sales.manage')<a href="{{ route('requisitions.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Requisition List</a><a href="{{ route('inventory-issues.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Issue List</a><a href="{{ route('issue-returns.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Issue Return List</a>@endcan @can('purchases.manage')<a href="{{ route('goods-receipts.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Receive List</a><a href="{{ route('item-transfers.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Item Transfer</a>@endcan @can('reports.view')<a href="{{ route('reports.stock-ledger') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Item StockLedger</a><a href="{{ route('reports.stock') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Issue Report</a>@endcan @can('suppliers.manage')<a href="{{ route('suppliers.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Supplier</a>@endcan</div></div>
        @endif

        @can('purchases.manage')
        <div x-data="{ open: {{ request()->is('admin/receives*') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Purchase &amp; Receive</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                <a href="{{ route('goods-receipts.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Goods Receive Add (MRR)</a>
                <a href="{{ route('goods-receipts.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Goods Receive List</a>
            </div>
        </div>
        @endcan

        {{-- Order & Sale Target moved below Meeting & Planning. --}}
        @if(false)
        <div x-data="{ open: {{ request()->is('sales/targets*') || request()->is('sales/customer-sales*') || request()->is('sales/collections*') || request()->is('sales/cheques*') || request()->is('sales/adjustments*') || request()->is('sales/returns*') || request()->is('sales/bike-report*') || request()->is('admin/reports/sales') || request()->is('admin/ledgers/customers') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Order &amp; Sales</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                @can('sales-targets.manage')
                    <a href="{{ route('sales-targets.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Target</a>
                    <a href="{{ route('sales-targets.performance') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Target Achievement Report</a>
                    <a href="{{ route('sales-targets.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Target Add</a>
                @endcan
                @can('sales.manage')
                    <a href="{{ route('customer-sales.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Customer Sale Entry</a>
                    <a href="{{ route('customer-sales.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sale List</a>
                    <a href="{{ route('sales-collections.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Collection Entry</a>
                    <a href="{{ route('sales-collections.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sale Collection</a>
                    <a href="{{ route('sales-collections.follow-up') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Payment Report</a>
                    <a href="{{ route('cheques.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Cheques</a>
                    <a href="{{ route('sales-adjustments.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Adjustment Entry</a>
                    <a href="{{ route('sales-adjustments.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Adjustment</a>
                @endcan
                @can('sales.return')
                    <a href="{{ route('sales-returns.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sale Return Entry</a>
                    <a href="{{ route('sales-returns.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sale Return List</a>
                @endcan
                @can('reports.view')
                    <a href="{{ route('reports.sales') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Vehicle Sales Report</a>
                    <a href="{{ route('ledgers.customers') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Customer Transaction Statement</a>
                @endcan
            </div>
        </div>
        @endif

        @can('sales.manage')
        <div x-data="{ open: {{ request()->is('sales/dealer*') || request()->is('sales/delivery*') || request()->is('sales/documents*') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Dealer &amp; Delivery</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                <a href="{{ route('dealer-outstanding.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Dealer Balance</a>
                <a href="{{ route('delivery-orders.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Delivery Order List</a>
                <a href="{{ route('dealer-delivery-challans.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Delivery Challan</a>
                <a href="{{ route('dealer-collections.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Dealer wise collection</a>
                <a href="{{ route('dealer-collections.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Dealer Collection List</a>
                <a href="{{ route('dealers.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Add New Dealer</a>
                <a href="{{ route('dealers.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Dealer List</a>
                @can('reports.view')<a href="{{ route('ledgers.customers') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Customer Balance</a>@endcan
            </div>
        </div>
        @endcan

        @if(auth()->user()->canAny(['products.manage','brands.manage','purchases.manage','reports.view']))
        <div x-data="{ open: {{ request()->is('admin/brands*') || request()->is('admin/item-categories*') || request()->is('admin/item-transfers*') || request()->is('admin/receives*') || request()->is('admin/reports/stock') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Stock &amp; Inventory</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                @can('products.manage')<a href="{{ route('item-categories.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Categories</a>@endcan
                @can('brands.manage')<a href="{{ route('brands.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Brands</a>@endcan
                @can('purchases.manage')<a href="{{ route('item-transfers.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Product Transfer</a><a href="{{ route('item-transfers.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Product Transfer List</a><a href="{{ route('goods-receipts.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Product Receive</a><a href="{{ route('goods-receipts.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Product Receive List</a>@endcan
                @can('reports.view')<a href="{{ route('reports.stock') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Stock Summary</a>@endcan
            </div>
        </div>
        @endif

        @can('accounts.manage')<div x-data="{ open: {{ request()->is('admin/accounts*') || request()->is('admin/vouchers*') || request()->is('admin/party-transactions*') ? 'true' : 'false' }} }"><button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Accounts</span><span :class="open ? 'rotate-90' : ''">›</span></button><div x-cloak x-show="open" class="space-y-0.5 pl-3"><a href="{{ route('accounts.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Chart of Accounts</a><a href="{{ route('party-transactions.create', 'supplier_payment') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Supplier Payment</a><a href="{{ route('party-transactions.index', 'supplier_payment') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Supplier Payment List</a><a href="{{ route('party-transactions.create', 'customer_receive') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Customer Receive</a><a href="{{ route('sales-clearance.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Sales Clearance</a><a href="{{ route('vouchers.create', 'credit') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Receive/Payment Entry</a><a href="{{ route('vouchers.index', 'credit') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Receive/Payment Vouchers</a><a href="{{ route('vouchers.create', 'journal') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Journal Voucher Entry</a><a href="{{ route('vouchers.index', 'journal') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Journal Vouchers</a><a href="{{ route('vouchers.create', 'contra') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Contra Voucher Entry</a><a href="{{ route('vouchers.index', 'contra') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Contra Vouchers</a></div></div>@endcan
        @include('layouts.navigation-financial-reports')

        @can('system-settings.manage')
        <div x-data="{ open: {{ request()->is('admin/system-settings*') || request()->is('crm/activity-setup*') ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>System Settings</span><span :class="open ? 'rotate-90' : ''">›</span></button>
            <div x-cloak x-show="open" class="space-y-0.5 pl-3">
                <a href="{{ route('system-settings.show', 'lead-sources') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Lead Source</a>
                <a href="{{ route('system-settings.show', 'pipeline-stages') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Lead Pipeline Stage</a>
                <a href="{{ route('system-settings.show', 'segments') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Segments</a>
                <a href="{{ route('system-settings.show', 'departments') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Department</a>
                <a href="{{ route('activity-setup.types.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Activity Type</a>
                <a href="{{ route('system-settings.show', 'event-types') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Event / Campaign Type</a>
                <a href="{{ route('system-settings.show', 'locations') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Location</a>
                <a href="{{ route('stores.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Showroom</a>
                <a href="{{ route('system-settings.show', 'notification-types') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Notification Type</a>
                <a href="{{ route('system-settings.show', 'email-settings') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Email Setting</a>
                <a href="{{ route('products.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Products</a>
            </div>
        </div>
        @endcan

        {{-- Financial Reports moved under Accounts. --}}

        @if(auth()->user()->canAny(['config.roles.manage','config.employees.manage','config.users.manage','config.store-positions.manage','system-settings.manage']))
        <div x-data="{ open: {{ request()->is('admin/roles*') || request()->is('admin/employees*') || request()->is('admin/store-positions*') || request()->is('admin/permissions*') ? 'true' : 'false' }} }"><button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2.5 font-semibold text-indigo-100 hover:bg-white/10"><span>Config</span><span :class="open ? 'rotate-90' : ''">›</span></button><div x-cloak x-show="open" class="space-y-0.5 pl-3">@can('config.store-positions.manage')<a href="{{ route('store-positions.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Showroom Positions</a>@endcan @can('config.employees.manage')<a href="{{ route('employees.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Employees</a><a href="{{ route('employees.create') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Add Employee</a>@endcan @can('config.roles.manage')<a href="{{ route('roles.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">User Roles</a><a href="{{ route('permissions.index') }}" class="block rounded px-3 py-2 text-indigo-200 hover:bg-white/10">Permissions</a>@endcan</div></div>
        @endif
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="shrink-0 border-t border-white/10 bg-[#141a28] px-3 py-3">@csrf<button class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-medium text-indigo-100 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/60">↪ <span>Sign out</span></button></form>
</aside>
