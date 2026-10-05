<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\HeldOrderController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware(['auth:sanctum', 'role:admin,manager,cashier,salesperson'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('catalog/products', [CatalogController::class, 'index']);
        Route::get('catalog/companies', [CatalogController::class, 'companies']);
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('sales', [SaleController::class, 'index']);
        Route::post('sales', [SaleController::class, 'store']);
        Route::get('pos/held-orders', [HeldOrderController::class, 'index']);
        Route::post('pos/held-orders', [HeldOrderController::class, 'store']);
        Route::get('pos/held-orders/{heldOrder}', [HeldOrderController::class, 'show']);
        Route::get('sales/{sale}', [SaleController::class, 'show']);
    });

    Route::get('stock', [CatalogController::class, 'index'])->middleware(['auth:sanctum', 'role:admin,manager,cashier']);

    Route::middleware(['auth:sanctum', 'role:admin,manager'])->group(function () {
        Route::get('admin/overview', [AdminController::class, 'overview']);
        Route::get('admin/reports/employee-sales', [AdminController::class, 'employeeSales']);
        Route::get('admin/master-data/{resource}', [MasterDataController::class, 'index']);
        Route::post('admin/master-data/{resource}', [MasterDataController::class, 'store']);
        Route::put('admin/master-data/{resource}/{id}', [MasterDataController::class, 'update']);
        Route::delete('admin/master-data/{resource}/{id}', [MasterDataController::class, 'destroy']);
    });
});
