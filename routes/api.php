<?php

use App\Http\Controllers\AbsentController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ExpenseIncomeController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\VillaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('verify.signature')->get('/my-secure-data', function () {
    return response()->json(['message' => 'This is secure data']);
});

Route::group(['prefix' => 'villa', 'middleware' => ['verify.signature']], function () {
    Route::get('index', [VillaController::class, 'index']);
    // Tambahkan route lainnya di sini
});

Route::group(['prefix' => 'expenseincome', 'middleware' => ['verify.signature']], function () {
    Route::get('income/{id}', [ExpenseIncomeController::class, 'indexIncome']);
    Route::get('expense/{id}', [ExpenseIncomeController::class, 'indexExpense']);
    Route::get('activity/{id}', [ExpenseIncomeController::class, 'expenseIncomeActivity']);
    Route::post('post', [ExpenseIncomeController::class, 'store']);
    // Tambahkan route lainnya di sini
});

Route::group(['prefix' => 'absent', 'middleware' => ['verify.signature']], function () {
    Route::get('index/{id}', [AbsentController::class, 'index']);
    Route::post('post', [AbsentController::class, 'store']);
    // Tambahkan route lainnya di sini
});

Route::group(['prefix' => 'schedule', 'middleware' => ['verify.signature']], function () {
    Route::get('index/{id}', [CalendarController::class, 'index']);
});

Route::group(['prefix' => 'pdf', 'middleware' => ['verify.signature']], function () {
    Route::get('data/{id}', [PDFController::class, 'getPDFdata']);
});
