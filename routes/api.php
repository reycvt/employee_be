<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware(['auth:api', 'role:superadmin'])->get('/users', function() {
    return \App\Models\User::all();
});

Route::middleware(['auth:api', 'role:admin'])->get('/welcome', function() {
    return response()->json(['message' => 'Welcome Admin!'], 200);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
// Route::get('users', [UserController::class, 'index'])->middleware('CheckRole:superadmin');

//upload
Route::middleware('auth:api')->group(function () {
    Route::post('upload', [FileController::class, 'upload']);
    Route::post('decrypt/{id}', [FileController::class, 'decrypt']);
    Route::get('download/{id}/{type}', [FileController::class, 'download']);
    Route::delete('delete/{id}', [FileController::class, 'delete']);
});

//enkrip dan dekrip
Route::get('encrypted-files', [FileController::class, 'encryptedFiles']);
Route::get('decrypted-files', [FileController::class, 'decryptedFiles']);
