<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Open API Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('products', ProductController::class);
Route::apiResource('users', UserController::class);
