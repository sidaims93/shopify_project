<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WebhooksController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/', [HomeController::class, 'base']);

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [HomeController::class, 'index'])->name('home');
    Route::prefix('shopify')->group(function () {
        Route::get('products', [ShopifyController::class, 'products'])->name('shopify.products');
        Route::get('orders', [ShopifyController::class,'orders'])->name('shopify.orders');
        Route::get('customers', [ShopifyController::class, 'customers'])->name('shopify.customers');
        Route::get('team', [TeamController::class, 'index'])->name('my.team');
        Route::get('settings', [SettingsController::class, 'settings'])->name('settings');
        Route::get('profile', [SettingsController::class, 'profile'])->name('my.profile');

        Route::prefix('sync')->group(function () {
            Route::get('customers', [ShopifyController::class, 'syncCustomers'])->name('customers.sync');
            Route::get('orders', [ShopifyController::class, 'syncOrders'])->name('orders.sync');
            Route::get('products', [ShopifyController::class, 'syncProducts'])->name('products.sync');
        });
    });
});

// /shopify/auth
Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [InstallationController::class, 'startInstallation']);
    Route::get('redirect', [InstallationController::class, 'handleRedirect'])->name('app_install_redirect');
    Route::get('complete', [InstallationController::class, 'completeInstallation'])->name('app_install_complete');
});

Route::prefix('webhook')->group(function () {
    Route::any('order/created', [WebhooksController::class, 'orderCreated']);
    Route::any('order/updated', [WebhooksController::class, 'orderUpdated']);
    Route::any('product/created', [WebhooksController::class, 'productCreated']);
    Route::any('app/uninstall', [WebhooksController::class, 'appUninstalled']);
    Route::any('shop/updated', [WebhooksController::class, 'shopUpdated']);
});

//Testing scripts
Route::get('configure/webhooks/{id}', [WebhooksController::class, 'configureWebhooks']);
Route::get('delete/webhooks/{id}', [WebhooksController::class, 'deleteWebhooks']);
