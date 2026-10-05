<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\GenericNameController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\AccountOpeningBalanceController;
use App\Http\Controllers\AccountVoucherController;
use App\Http\Controllers\PartyTransactionController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\PartyLedgerController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PerformanceReportController;
use App\Http\Controllers\ActivitySetupController;
use App\Http\Controllers\PromotionEventController;
use App\Http\Controllers\EventBudgetController;
use App\Http\Controllers\BulkSmsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified', 'permission:dashboard.view'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'permission:pos.access'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/catalog', [PosController::class, 'catalog'])->name('pos.catalog');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::post('/pos/saved-orders', [PosController::class, 'saveOrder'])->name('pos.saved-orders.store');
    Route::get('/pos/saved-orders', [PosController::class, 'savedOrders'])->name('pos.saved-orders.index');
    Route::get('/pos/saved-orders/{savedOrder}/edit', [PosController::class, 'editSavedOrder'])->name('pos.saved-orders.edit');
    Route::delete('/pos/saved-orders/{savedOrder}', [PosController::class, 'destroySavedOrder'])->name('pos.saved-orders.destroy');
    Route::get('/pos/history', [PosController::class, 'history'])->name('pos.history');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
});

Route::middleware(['auth', 'permission:products.view'])->prefix('admin')->group(function () {
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
});

Route::middleware(['auth', 'permission:suppliers.manage'])->prefix('admin')->group(function () {
    Route::resource('suppliers', SupplierController::class)->only(['index', 'create', 'store']);
    Route::resource('suppliers', SupplierController::class)->only(['edit', 'update', 'destroy']);
});
Route::middleware(['auth', 'permission:customers.manage'])->prefix('admin')->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store']);
    Route::resource('customers', CustomerController::class)->only(['edit', 'update', 'destroy']);
});
Route::middleware(['auth', 'permission:activities.manage'])->prefix('crm')->group(function () {
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    Route::get('activity-types/{activityType}/sub-types', [ActivityController::class, 'subTypes'])->name('activities.sub-types');
    Route::get('activity-subjects', [ActivityController::class, 'subjects'])->name('activities.subjects');
});
Route::middleware(['auth', 'permission:leads.manage'])->prefix('crm')->group(function () {
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
});
Route::middleware(['auth', 'permission:todos.manage'])->prefix('crm')->group(function () {
    Route::get('todos', [TodoController::class, 'index'])->name('todos.index');
    Route::get('todos/create', [TodoController::class, 'create'])->name('todos.create');
    Route::post('todos', [TodoController::class, 'store'])->name('todos.store');
    Route::patch('todos/{todo}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    Route::get('todo-subjects', [TodoController::class, 'subjects'])->name('todos.subjects');
});
Route::middleware(['auth', 'permission:contacts.manage'])->prefix('crm')->group(function () { Route::get('contacts',[ContactController::class,'index'])->name('contacts.index'); Route::get('contacts/create',[ContactController::class,'create'])->name('contacts.create'); Route::post('contacts',[ContactController::class,'store'])->name('contacts.store'); });
Route::middleware(['auth', 'permission:organizations.manage'])->get('crm/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
Route::middleware(['auth', 'permission:performance-reports.view'])->get('crm/performance-report', [PerformanceReportController::class, 'index'])->name('performance.index');
Route::middleware(['auth', 'permission:activity-setup.manage'])->prefix('crm/activity-setup')->group(function () {
    Route::get('/', [ActivitySetupController::class, 'index'])->name('activity-setup.index');
    Route::post('types', [ActivitySetupController::class, 'storeType'])->name('activity-setup.types.store');
    Route::put('types/{activityType}', [ActivitySetupController::class, 'updateType'])->name('activity-setup.types.update');
    Route::delete('types/{activityType}', [ActivitySetupController::class, 'destroyType'])->name('activity-setup.types.destroy');
    Route::post('sub-types', [ActivitySetupController::class, 'storeSubType'])->name('activity-setup.sub-types.store');
    Route::put('sub-types/{activitySubType}', [ActivitySetupController::class, 'updateSubType'])->name('activity-setup.sub-types.update');
    Route::delete('sub-types/{activitySubType}', [ActivitySetupController::class, 'destroySubType'])->name('activity-setup.sub-types.destroy');
    Route::post('subject-types', [ActivitySetupController::class, 'storeSubjectType'])->name('activity-setup.subject-types.store');
    Route::put('subject-types/{activitySubjectType}', [ActivitySetupController::class, 'updateSubjectType'])->name('activity-setup.subject-types.update');
    Route::delete('subject-types/{activitySubjectType}', [ActivitySetupController::class, 'destroySubjectType'])->name('activity-setup.subject-types.destroy');
});
Route::middleware(['auth', 'permission:events.manage'])->prefix('promotion')->group(function () { Route::get('events',[PromotionEventController::class,'index'])->name('events.index'); Route::get('events/create',[PromotionEventController::class,'create'])->name('events.create'); Route::post('events',[PromotionEventController::class,'store'])->name('events.store'); });
Route::middleware(['auth', 'permission:budgets.manage'])->prefix('promotion')->group(function () { Route::get('budgets',[EventBudgetController::class,'index'])->name('budgets.index'); Route::get('budgets/create',[EventBudgetController::class,'create'])->name('budgets.create'); Route::post('budgets',[EventBudgetController::class,'store'])->name('budgets.store'); Route::get('events/{event}/budget-info',[EventBudgetController::class,'event'])->name('budgets.event'); });
Route::middleware(['auth', 'permission:bulk-sms.manage'])->prefix('promotion')->group(function(){Route::get('bulk-sms',[BulkSmsController::class,'create'])->name('bulk-sms.create');Route::post('bulk-sms',[BulkSmsController::class,'store'])->name('bulk-sms.store');});
Route::middleware(['auth', 'permission:purchases.manage'])->prefix('admin')->group(function () {
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
});

Route::middleware(['auth', 'permission:accounts.manage'])->prefix('admin')->group(function () {
    Route::get('accounts/sub-accounts', [ChartOfAccountController::class, 'subAccounts'])->name('accounts.sub-accounts');
    Route::get('accounts/opening-balances', [AccountOpeningBalanceController::class, 'index'])->name('accounts.opening-balances.index');
    Route::post('accounts/opening-balances', [AccountOpeningBalanceController::class, 'store'])->name('accounts.opening-balances.store');
    Route::resource('accounts', ChartOfAccountController::class)->except(['show']);
    Route::get('vouchers/{type}/create', [AccountVoucherController::class, 'create'])->whereIn('type', ['debit', 'credit', 'journal', 'contra'])->name('vouchers.create');
    Route::post('vouchers/{type}', [AccountVoucherController::class, 'store'])->whereIn('type', ['debit', 'credit', 'journal', 'contra'])->name('vouchers.store');
    Route::get('vouchers/{type}', [AccountVoucherController::class, 'index'])->whereIn('type', ['debit', 'credit', 'journal', 'contra'])->name('vouchers.index');
    Route::get('voucher/{voucher}', [AccountVoucherController::class, 'show'])->name('vouchers.show');
    Route::get('party-transactions/{type}/create', [PartyTransactionController::class, 'create'])->whereIn('type', ['customer_receive', 'supplier_payment'])->name('party-transactions.create');
    Route::post('party-transactions/{type}', [PartyTransactionController::class, 'store'])->whereIn('type', ['customer_receive', 'supplier_payment'])->name('party-transactions.store');
    Route::get('party-transactions/{type}', [PartyTransactionController::class, 'index'])->whereIn('type', ['customer_receive', 'supplier_payment'])->name('party-transactions.index');
    Route::get('party-transaction/{transaction}', [PartyTransactionController::class, 'show'])->name('party-transactions.show');
    Route::get('ledgers/customers', [PartyLedgerController::class, 'customer'])->name('ledgers.customers');
    Route::get('ledgers/suppliers', [PartyLedgerController::class, 'supplier'])->name('ledgers.suppliers');
});

Route::middleware(['auth', 'permission:brands.manage'])->prefix('admin')->group(function () {
    Route::resource('brands', BrandController::class)->except(['show']);
});
Route::middleware(['auth', 'permission:generic-names.manage'])->prefix('admin')->group(function () {
    Route::resource('generic-names', GenericNameController::class)->except(['show']);
});
Route::middleware(['auth', 'permission:products.manage'])->prefix('admin')->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});
Route::middleware(['auth', 'permission:employees.manage'])->prefix('admin')->group(function () {
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
    Route::get('payrolls/create', [PayrollController::class, 'create'])->name('payrolls.create');
    Route::post('payrolls', [PayrollController::class, 'store'])->name('payrolls.store');
    Route::get('payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
    Route::post('payrolls/{payroll}/post', [PayrollController::class, 'post'])->name('payrolls.post');
    Route::get('payroll-items/{item}/payment', [PayrollController::class, 'paymentCreate'])->name('salary-payments.create');
    Route::post('payroll-items/{item}/payment', [PayrollController::class, 'paymentStore'])->name('salary-payments.store');
    Route::get('salary-payments/{payment}', [PayrollController::class, 'paymentShow'])->name('salary-payments.show');
});

Route::middleware(['auth', 'permission:reports.view'])->prefix('admin')->group(function () {
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/product-supplier-sales', [ReportController::class, 'productSupplierSales'])->name('reports.product-supplier-sales');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/product-needed', [ReportController::class, 'needed'])->name('reports.needed');
    Route::get('reports/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('reports/trial-balance', [FinancialReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('reports/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('reports/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
});

Route::middleware(['auth', 'permission:employees.manage'])->prefix('admin')->group(function () {
    Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);
});

Route::middleware(['auth', 'permission:settings.manage'])->prefix('admin')->group(function () {
    Route::resource('roles', RolePermissionController::class)->except(['show']);
});

require __DIR__.'/auth.php';
