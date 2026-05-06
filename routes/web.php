<?php
// ============================================================
// routes/web.php — All URL Routes for DrapeStore
//
// This file defines what URL goes to which controller.
// Think of it as a map: URL → Controller → View (page)
// ============================================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// ── HOME ─────────────────────────────────────────────────────
// When someone visits "/" redirect them to the shop
Route::get('/', fn() => redirect()->route('shop.index'));

// ── SHOP ROUTES ───────────────────────────────────────────────
// Public pages — anyone can visit these (no login needed)
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/',           [ShopController::class, 'index'])->name('index');   // /shop
    Route::get('/{product}',  [ShopController::class, 'show'])->name('show');     // /shop/1
});

// ── CART ROUTES ───────────────────────────────────────────────
// Shopping cart — stored in PHP session (no login needed)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',           [CartController::class, 'index'])->name('index');   // View cart
    Route::post('/add',       [CartController::class, 'add'])->name('add');       // Add item
    Route::post('/update',    [CartController::class, 'update'])->name('update'); // Change qty
    Route::post('/remove',    [CartController::class, 'remove'])->name('remove'); // Remove item
    Route::post('/clear',     [CartController::class, 'clear'])->name('clear');   // Empty cart
});

// ── CHECKOUT ROUTES ───────────────────────────────────────────
// PCI DSS REQ 4: These routes MUST use HTTPS in production
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/',           [CheckoutController::class, 'index'])->name('index');     // Checkout form
    Route::post('/process',   [CheckoutController::class, 'process'])->name('process'); // Process payment
    Route::get('/success',    [CheckoutController::class, 'success'])->name('success'); // Confirmation
});

// ── AUTH ROUTES ───────────────────────────────────────────────
// PCI DSS REQ 8: Authentication (login, logout)
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login',      [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',     [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout',    [AuthController::class, 'logout'])->name('logout');
    Route::get('/register',   [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',  [AuthController::class, 'register'])->name('register.post');
});

// ── ADMIN ROUTES ──────────────────────────────────────────────
// PCI DSS REQ 7: Access restricted — admin role required
// The 'admin' middleware checks the user is logged in AND has role='admin'
Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {
    Route::get('/',           [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders',     [AdminController::class, 'orders'])->name('orders');
    Route::get('/products',   [AdminController::class, 'products'])->name('products');
    Route::post('/products',  [AdminController::class, 'storeProduct'])->name('products.store');
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('products.delete');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit');
});
