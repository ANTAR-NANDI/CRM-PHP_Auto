<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\CustomerVehicleSale;
use App\Models\User;
use App\Services\AccountingPostingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostExistingVehicleSales extends Command
{
    protected $signature = 'accounting:post-existing-vehicle-sales {--user= : User ID used only where the sale has no assigned sales person}';

    protected $description = 'Post unposted vehicle sales and sales collections to accounting exactly once.';

    public function handle(AccountingPostingService $posting): int
    {
        $fallbackUser = $this->option('user') ? User::find($this->option('user')) : null;
        $salesPosted = 0;
        $collectionsPosted = 0;

        CustomerVehicleSale::query()->whereNull('account_voucher_id')->orderBy('id')->each(function (CustomerVehicleSale $sale) use ($posting, $fallbackUser, &$salesPosted): void {
            $user = User::find($sale->sales_person_id) ?: $fallbackUser;
            if (! $user) {
                throw new \RuntimeException("Vehicle sale {$sale->sale_no} has no sales person. Re-run with --user=<user ID>.");
            }

            DB::transaction(function () use ($sale, $user, $posting, &$salesPosted): void {
                $lockedSale = CustomerVehicleSale::query()->with('customer')->lockForUpdate()->findOrFail($sale->id);
                if ($lockedSale->account_voucher_id) return;
                $voucher = $posting->postCustomerVehicleSale($lockedSale, $user);
                $lockedSale->update(['account_voucher_id' => $voucher->id]);
                $salesPosted++;
            });
        });

        DB::table('sales_collections')->whereNull('account_voucher_id')->where('collection_type', 'sales')->orderBy('id')->each(function (object $collection) use ($posting, $fallbackUser, &$collectionsPosted): void {
            $sale = CustomerVehicleSale::findOrFail($collection->customer_vehicle_sale_id);
            $user = User::find($sale->sales_person_id) ?: $fallbackUser;
            if (! $user) {
                throw new \RuntimeException("Collection {$collection->id} belongs to {$sale->sale_no}, which has no sales person. Re-run with --user=<user ID>.");
            }
            $code = Str::before((string) $collection->bank_head, ' — ');
            $account = ChartOfAccount::query()->where('code', $code)->where('is_active', true)->first();
            if (! $account) {
                throw new \RuntimeException("Collection {$collection->id} has no valid cash or bank account.");
            }

            DB::transaction(function () use ($collection, $sale, $user, $account, $posting, &$collectionsPosted): void {
                $lockedCollection = DB::table('sales_collections')->lockForUpdate()->find($collection->id);
                if ($lockedCollection->account_voucher_id) return;
                $voucher = $posting->postCustomerVehicleCollection($sale->load('customer'), $account, (float) $collection->amount, $collection->received_at, $user, 'Vehicle collection '.$sale->sale_no);
                DB::table('sales_collections')->where('id', $collection->id)->update(['account_voucher_id' => $voucher->id, 'updated_at' => now()]);
                $collectionsPosted++;
            });
        });

        $this->info("Posted {$salesPosted} vehicle sale(s) and {$collectionsPosted} sales collection(s).");

        return self::SUCCESS;
    }
}
