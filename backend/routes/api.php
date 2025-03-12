<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LessorController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminLogoutController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;



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