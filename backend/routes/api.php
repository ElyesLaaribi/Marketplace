<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LessorController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\AdminAddController;
use App\Http\Controllers\Reviews\ReviewController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminLogoutController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Listings\ListingsController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\Listings\RetrieveListingsController;




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

// Reviews
Route::apiResource('/reviews', ReviewController::class);


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

Route::middleware('auth:admin')->apiResource('/users', UsersController::class);
Route::middleware('auth:admin')->apiResource('/admins', AdminController::class); 
Route::middleware('auth:admin')->post('/add-admin', AdminAddController::class);