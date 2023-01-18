<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\Insurer\ImgController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ImgProductController;
use App\Http\Controllers\TrawickProductController;
use App\Http\Controllers\ProviderController;
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
Route::post('product', [QuoteController::class, 'getProduct']);
Route::post('price', [QuoteController::class, 'getPrice']);
Route::post('purchase', [QuoteController::class, 'purchase']);
Route::post('testQuote', [QuoteController::class, 'testTravelsafe']);
Route::post('purchaseTravelInsured', [QuoteController::class, 'purchaseTravelInsured']);
Route::post('purchaseImg', [QuoteController::class, 'purchaseImg']);
Route::post('purchaseTrawick', [QuoteController::class, 'purchaseTrawick']);
Route::get('states', [StateController::class, 'index']);
Route::get('countries', [CountryController::class, 'index']);
Route::get('providers', [ProviderController::class, 'index']);
Route::post('providers/{provider}/toggleStatus', [ProviderController::class, 'toggleStatus']);
Route::get('providers/{provider}/products', [ProviderController::class, 'products']);
Route::post('providers/{provider}/products/{id}/toggleStatus', [ProviderController::class, 'toggleProductStatus']);
Route::post('quote-travel-insured', [QuoteController::class, 'quoteTravelInsured']);
Route::post('purchase-travel-insured', [QuoteController::class, 'purchaseTravelInsured']);
Route::post('quote-img', [ImgController::class, 'quote']);
Route::post('purchase-img', [ImgController::class, 'purchase']);

Route::get('imgProducts', [ImgProductController::class, 'index']);
Route::post('imgQuote', [ImgProductController::class, 'quote']);
Route::get('trawickProducts', [TrawickProductController::class, 'index']);