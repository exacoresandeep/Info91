<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'auth'], function ($router)
{
    Route::post('/register', [AuthController::class, 'register']);
	Route::post('/verify_otp', [AuthController::class, 'verify_otp']);
	Route::post('/resend_otp', [AuthController::class, 'resend_otp']);
	Route::post('/refresh_token', [Authcontroller::class , 'refresh_token'])->name('refresh_token');
});

Route::group(['middleware' => ['jwt.verify']], function ()
{
	Route::get('/user_profile', [AuthController::class, 'userProfile']);    
	Route::post('/update_profile', [AuthController::class, 'update_profile']);
});

