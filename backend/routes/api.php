<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LessorController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ReservationDates;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Listings\ListingBI;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AdminAddController;
use App\Http\Controllers\Reviews\ReviewController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminLogoutController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Listings\ListingsController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\Listings\RetrieveListingsController;
use App\Http\Controllers\Admin\AdminBIController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// clients and lessors 
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/lessor', LessorController::class);
Route::middleware('auth:sanctum')->get('/profile', [ProfileController::class, 'profile']);
Route::middleware('auth:sanctum')->post('/change-password', [ProfileController::class, 'changePassword']);
Route::middleware('auth:sanctum')->post('/update-profile', [ProfileController::class, 'updateProfile']);

// Listings 
Route::middleware('auth:sanctum')->apiResource('/listings', ListingsController::class);
Route::apiResource('/public-listings', RetrieveListingsController::class)
    ->parameters(['public-listings' => 'listing']);
Route::get('/public-listings/category/{category}', [RetrieveListingsController::class, 'getCategoryProducts']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lessor/listings/count', [ListingBI::class, 'countListings']);
    Route::get('/lessor/listings/demand', [ListingBI::class, 'countReservations']);
    Route::get('/lessor/listings/revenue', [ListingBI::class, 'getEarnings']);
    Route::get('/lessor/listings/revenuePerListing', [ListingBI::class, 'getRevenuePerListing']);
    Route::get('/lessor/listings/clients', [ListingBI::class, 'getTotalClients']);
    
    // New BI dashboard endpoints
    Route::get('/lessor/listings/average-revenue', [ListingBI::class, 'getAverageRevenue']);
    Route::get('/lessor/listings/occupancy', [ListingBI::class, 'getOccupancy']);
    Route::get('/lessor/listings/popular-items', [ListingBI::class, 'getPopularItems']);
    Route::get('/lessor/listings/top-clients', [ListingBI::class, 'getTopClients']);
});


// Reviews
Route::apiResource('/reviews', ReviewController::class);

// Reservation
Route::middleware('auth:sanctum')->apiResource('reservations', ReservationController::class);
// Reservaton dates
Route::get('/listings/{listingId}/reservations', [ReservationDates::class, 'byListing']);


// Category
Route::apiResource('/categories', CategoryController::class);

Route::post('/login', LoginController::class);
Route::post('/register', RegisterController::class);
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::post('/forgot', [ResetPassController::class, 'forgot']);
Route::post('/reset', [ResetPassController::class, 'reset']);


// admin
Route::post('/admin/login', AdminLoginController::class);
Route::middleware('auth:sanctum')->post('/admin/logout', AdminLogoutController::class);

Route::middleware('auth:admin')->get('/admin', function (Request $request) {
    return $request->user(); 
});

// Admin BI Dashboard Routes
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/clients/count', [AdminBIController::class, 'getTotalClients']);
    Route::get('/admin/lessors/count', [AdminBIController::class, 'getTotalLessors']);
    Route::get('/admin/categories/count', [AdminBIController::class, 'getTotalCategories']);
    Route::get('/admin/lessors/top', [AdminBIController::class, 'getTopLessors']);
    Route::get('/admin/categories', [AdminBIController::class, 'getCategories']);
});

Route::middleware('auth:admin')->apiResource('/users', UsersController::class);
Route::middleware('auth:admin')->apiResource('/admins', AdminController::class); 
Route::middleware('auth:admin')->post('/add-admin', AdminAddController::class);

Route::middleware('auth:sanctum')->post('/users/update-device-token', [DeviceTokenController::class, 'updateDeviceToken']);

Route::middleware('auth:sanctum')->post('/send-test-notification', [NotificationController::class, 'sendTest']);
Route::middleware('auth:sanctum')->post('/test-direct-fcm', [NotificationController::class, 'testDirectFCM']);


// Lessor BI Endpoints
Route::middleware(['auth:sanctum'])->prefix('lessor/listings')->group(function () {
    Route::get('/average-revenue', [ListingBI::class, 'getAverageRevenue']);
    Route::get('/occupancy', [ListingBI::class, 'getOccupancy']);
    Route::get('/popular-items', [ListingBI::class, 'getPopularItems']);
    Route::get('/top-clients', [ListingBI::class, 'getTopClients']);
});
        
// Admin BI Endpoints
Route::get('/admin/clients/count', [AdminBIController::class, 'getTotalClients']);
Route::get('/admin/lessors/count', [AdminBIController::class, 'getTotalLessors']);
Route::get('/admin/categories/count', [AdminBIController::class, 'getTotalCategories']);
Route::get('/admin/lessors/top', [AdminBIController::class, 'getTopLessors']);
Route::get('/admin/categories', [AdminBIController::class, 'getCategories']);

