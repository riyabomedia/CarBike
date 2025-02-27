<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home page
Route::get('/', function () {
    return redirect()->route('vehicles.index');
});

// Vehicle routes
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{slug}', [VehicleController::class, 'show'])->name('vehicles.show');
Route::get('/vehicles/brand/{brand}', [VehicleController::class, 'byBrand'])->name('vehicles.by-brand');
Route::get('/vehicles/type/{type}', [VehicleController::class, 'byType'])->name('vehicles.by-type');
Route::get('/models-by-brand', [VehicleController::class, 'getModelsByBrand'])->name('vehicles.models-by-brand');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Vehicle management
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
    
    // Comparison
    Route::get('/comparison', [ComparisonController::class, 'index'])->name('comparison.index');
    Route::post('/comparison/add', [ComparisonController::class, 'add'])->name('comparison.add');
    Route::delete('/comparison/remove', [ComparisonController::class, 'remove'])->name('comparison.remove');
    Route::post('/comparison/clear', [ComparisonController::class, 'clear'])->name('comparison.clear');
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Vehicle approval
        Route::get('/vehicles/pending', [AdminController::class, 'pendingVehicles'])->name('vehicles.pending');
        Route::post('/vehicles/{vehicle}/approve', [AdminController::class, 'approveVehicle'])->name('vehicles.approve');
        Route::delete('/vehicles/{vehicle}/reject', [AdminController::class, 'rejectVehicle'])->name('vehicles.reject');
        
        // User management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        
        // Brand management
        Route::get('/brands', [AdminController::class, 'brands'])->name('brands');
        Route::get('/brands/create', [AdminController::class, 'createBrand'])->name('brands.create');
        Route::post('/brands', [AdminController::class, 'storeBrand'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [AdminController::class, 'editBrand'])->name('brands.edit');
        Route::put('/brands/{brand}', [AdminController::class, 'updateBrand'])->name('brands.update');
        
        // Model management
        Route::get('/models', [AdminController::class, 'models'])->name('models');
        Route::get('/models/create', [AdminController::class, 'createModel'])->name('models.create');
        Route::post('/models', [AdminController::class, 'storeModel'])->name('models.store');
        Route::get('/models/{model}/edit', [AdminController::class, 'editModel'])->name('models.edit');
        Route::put('/models/{model}', [AdminController::class, 'updateModel'])->name('models.update');
        
        // Import
        Route::get('/import', [ImportController::class, 'index'])->name('import');
        Route::post('/import', [ImportController::class, 'import'])->name('import.process');
    });
    
    // Dealer routes
    Route::middleware(['role:dealer'])->prefix('dealer')->name('dealer.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dealer.dashboard');
        })->name('dashboard');
        
        // Import for dealers
        Route::get('/import', [ImportController::class, 'index'])->name('import');
        Route::post('/import', [ImportController::class, 'import'])->name('import.process');
    });
});

// Authentication routes (Laravel Breeze)
require __DIR__.'/auth.php'; 