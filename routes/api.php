<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\Insurer\ImgController;
use App\Http\Controllers\StateController;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum'])->get('/admin/user', function (Request $request) {
    return $request->user();
});

Route::post('quote', [QuoteController::class, 'index']);
Route::post('purchaseTravelInsured', [QuoteController::class, 'purchaseTravelInsured']);
Route::get('states', [StateController::class, 'index']);
Route::post('quote-travel-insured', [QuoteController::class, 'quoteTravelInsured']);
Route::post('purchase-travel-insured', [QuoteController::class, 'purchaseTravelInsured']);
Route::post('quote-img', [ImgController::class, 'quote']);
Route::post('purchase-img', [ImgController::class, 'purchase']);