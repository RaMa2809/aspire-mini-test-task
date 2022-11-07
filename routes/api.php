<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\LoanController;
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
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);


Route::prefix('/loan')->middleware('auth:sanctum')->group(function () {
    Route::get('/all',[LoanController::class,'index'])->name('view-loan');
    Route::post('/create',[LoanController::class,'store'])->name('create-loan');
    Route::post('/approve-loan',[LoanController::class,'approve_loan'])->name('approve-loan');
    Route::post('/repay-loan',[LoanController::class,'repay_loan'])->name('repay-loan');
});
