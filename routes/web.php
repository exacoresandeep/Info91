<?php

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

Route::get('public/group_profile_pic/{filename}', function ($filename) {
    $path = public_path('group_profile_pic/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
Route::get('public/group_banner_images/{filename}', function ($filename) {
    $path = public_path('group_banner_images/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
Route::get('public/group_cover_pic/{filename}', function ($filename) {
    $path = public_path('group_cover_pic/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
Route::get('public/profile_pic/{filename}', function ($filename) {
    $path = public_path('profile_pic/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

Route::get('public/upload_files/{filename}', function ($filename) {
    $path = public_path('upload_files/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
