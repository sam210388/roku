<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RealisasiSemar;
use App\Http\Controllers\API\RealisasiSakti;

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

Route::get('realisasisemar/{nomorkarwas}',[RealisasiSemar::class, 'getStatusKarwas'])->name('statusrealisasisemar');
Route::get('realisasisakti/{nomorspm}',[RealisasiSakti::class, 'getStatusSpm'])->name('statusrealisasisakti');

