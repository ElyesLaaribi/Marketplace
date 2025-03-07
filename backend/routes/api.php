<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LessorController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/lessor', LessorController::class);

Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');


Route::post('/login', LoginController::class);
Route::post('/register', RegisterController::class);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);


