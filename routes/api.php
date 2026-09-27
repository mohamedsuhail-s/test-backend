<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Open API Routes
|--------------------------------------------------------------------------
|
| - POST   /api/products      (Create API)
| - PUT    /api/products/{id} (Edit / Update API)
| - DELETE /api/products/{id} (Delete API)
| - GET    /api/products      (List API)
| - GET    /api/products/{id} (Show API)
|
*/

Route::apiResource('products', ProductController::class);
