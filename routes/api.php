<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('mpesa/password', [MpesaController::class, 'lipaNaMpesaPassword']);
Route::post('mpesa/access/token', [MpesaController::class, 'generateAccessToken']);
Route::post('mpesa/stk/push', [MpesaController::class, 'stkPush']);
Route::post('stk/push/callback/url', [MpesaController::class, 'MpesaApiResponse']);
