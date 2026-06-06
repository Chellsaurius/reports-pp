<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando'
    ]);
});

Route::post('/reports/sales', [ReportController::class, 'sales']);
// Route::apiResource('products', ProductController::class);
Route::apiResource('productos', ProductController::class);