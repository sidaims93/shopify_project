<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\SuperAdminController;
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

Route::middleware(['auth', 'permission:all-access'])->group(function () {
    Route::resource('stores', SuperAdminController::class);
    Route::get('notifications', [SuperAdminController::class, 'sendIndex'])->name('real.time.notifications');
    Route::post('send/message', [SuperAdminController::class, 'sendMessage'])->name('send.web.message');
});

Route::middleware('auth')->group(function () {
    
    Route::get('dashboard', [HomeController::class, 'index'])->name('home');

    Route::middleware(['role:Admin', 'is_public_app'])->group(function () {
        Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('plan/buy/{id}', [BillingController::class, 'buyThisPlan'])->name('plan.buy');
        Route::any('shopify/rac/accept', [BillingController::class, 'acceptSubscriptionCharge'])->name('plan.accept');
        Route::get('consume/credits', [BillingController::class, 'consumeCredits'])->name('consume.credits');
    });
    
    Route::prefix('shopify')->group(function () {
        Route::middleware('permission:write-products|read-products')->group(function () {
            Route::get('products', [ShopifyController::class, 'products'])->name('shopify.products');
            Route::get('sync/products', [ShopifyController::class, 'syncProducts'])->name('products.sync');
        });
        Route::middleware('permission:write-orders|read-orders')->group(function () {
            Route::get('orders', [ShopifyController::class,'orders'])->name('shopify.orders');
            Route::get('sync/orders', [ShopifyController::class, 'syncOrders'])->name('orders.sync');
        });
        Route::middleware('permission:write-customers|read-customers')->group(function () {
            Route::get('customers', [ShopifyController::class, 'customers'])->name('shopify.customers');
            Route::get('sync/customers', [ShopifyController::class, 'syncCustomers'])->name('customers.sync');     
        });
        Route::get('profile', [SettingsController::class, 'profile'])->name('my.profile');
        Route::any('accept/charge', [ShopifyController::class, 'acceptCharge'])->name('accept.charge');
    });
    
    Route::get('settings', [SettingsController::class, 'settings'])->name('settings');
        
    Route::middleware(['permission:write-members|read-members', 'is_public_app'])->group(function () {
        Route::resource('members', TeamController::class);
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
