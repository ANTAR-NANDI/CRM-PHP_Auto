<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Services\PartyAccountService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn ($user) => $user->isAdministrator() ? true : null);
        Customer::created(fn (Customer $customer) => app(PartyAccountService::class)->forCustomer($customer));
        Customer::updated(fn (Customer $customer) => $customer->wasChanged('name') ? app(PartyAccountService::class)->forCustomer($customer) : null);
        Supplier::created(fn (Supplier $supplier) => app(PartyAccountService::class)->forSupplier($supplier));
        Supplier::updated(fn (Supplier $supplier) => $supplier->wasChanged('name') ? app(PartyAccountService::class)->forSupplier($supplier) : null);
    }
}
