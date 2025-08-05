<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\Auth\LoginController;

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

// Public routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('restaurants', RestaurantController::class);
    Route::get('/system-settings', [DashboardController::class, 'systemSettings'])->name('system.settings');
});

// Restaurant Management routes
Route::middleware(['auth', 'permission:manage restaurants|manage menu|manage orders'])->group(function () {
    
    // Restaurant routes
    Route::resource('restaurants', RestaurantController::class)->except(['create', 'store']);
    
    // Menu Management
    Route::resource('categories', CategoryController::class);
    Route::resource('menu-items', MenuItemController::class);
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/public/{restaurant}', [MenuController::class, 'publicMenu'])->name('menu.public');
    
    // Order Management
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/kitchen', [OrderController::class, 'kitchen'])->name('kitchen.index');
    
    // Table Management
    Route::resource('tables', TableController::class);
    Route::post('/tables/{table}/update-status', [TableController::class, 'updateStatus'])->name('tables.update-status');
    Route::get('/floor-plan', [TableController::class, 'floorPlan'])->name('floor-plan');
    
    // Reservation Management
    Route::resource('reservations', ReservationController::class);
    Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/seat', [ReservationController::class, 'seat'])->name('reservations.seat');
    Route::post('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
    
    // Customer Management
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
    
    // Staff Management
    Route::middleware(['permission:manage staff'])->group(function () {
        Route::resource('staff', StaffController::class);
        Route::post('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    });
});

// API Routes for AJAX calls
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/dashboard-stats', [DashboardController::class, 'dashboardStats'])->name('dashboard.stats');
    Route::get('/orders/pending', [OrderController::class, 'pendingOrders'])->name('orders.pending');
    Route::get('/tables/status', [TableController::class, 'tableStatus'])->name('tables.status');
    Route::get('/menu-items/search', [MenuItemController::class, 'search'])->name('menu-items.search');
});

// Customer portal routes
Route::middleware(['auth:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/menu/{restaurant}', [MenuController::class, 'customerMenu'])->name('menu');
    Route::get('/orders', [CustomerController::class, 'myOrders'])->name('orders');
    Route::get('/reservations', [CustomerController::class, 'myReservations'])->name('reservations');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});

// Public restaurant menu (for QR codes, etc.)
Route::get('/restaurant/{restaurant}/menu', [MenuController::class, 'publicMenu'])->name('public.menu');
Route::get('/restaurant/{restaurant}/order', [OrderController::class, 'publicOrder'])->name('public.order');
