<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModalityController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;

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

Route::post('login', [AuthController::class, 'login']);

Route::get('validateToken', [AuthController::class, 'validateToken']);
Route::post('recoverPassword', [UserController::class, 'passwordRecovery']);
Route::post('updatePassword', [UserController::class, 'updatePassword']);


Route::get('validateToken', [AuthController::class, 'validateToken']);

Route::middleware('jwt')->group(function(){

    Route::middleware(AdminMiddleware::class)->group(function() {
        // Middleware do admin
    });

    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('user')->group(function(){
        Route::get('all', [UserController::class, 'all']);
        Route::get('search', [UserController::class, 'search']);
        Route::get('cards', [UserController::class, 'cards']);
        Route::get('me', [UserController::class, 'getUser']);
        Route::post('create', [UserController::class, 'create']);
        Route::patch('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'delete']);
        Route::post('block/{id}', [UserController::class, 'userBlock']);
    });

    Route::prefix('tender')->group(function(){
        Route::get('all', [TenderController::class, 'all']);
        Route::get('search', [TenderController::class, 'search']);
        Route::post('create', [TenderController::class, 'create']);

        Route::delete('attachment/{attachmentId}', [TenderController::class, 'deleteAttachment']);
        Route::delete('item/{itemId}', [TenderController::class, 'deleteItem']);
        Route::delete('task/{taskId}', [TenderController::class, 'deleteTask']);

        Route::patch('{tender_id}/status', [TenderController::class, 'updateStatus']);
        
        Route::patch('{id}', [TenderController::class, 'update']);
        Route::delete('{id}', [TenderController::class, 'delete']);
    });

    Route::prefix('modality')->group(function(){
        Route::get('all', [ModalityController::class, 'all']);
        Route::get('search', [ModalityController::class, 'search']);
        Route::post('create', [ModalityController::class, 'create']);
        Route::patch('{id}', [ModalityController::class, 'update']);
        Route::delete('{id}', [ModalityController::class, 'delete']);
    });

    Route::prefix('status')->group(function(){
        Route::get('all', [StatusController::class, 'all']);
        Route::get('search', [StatusController::class, 'search']);
        Route::post('create', [StatusController::class, 'create']);
        Route::patch('{id}', [StatusController::class, 'update']);
        Route::delete('{id}', [StatusController::class, 'delete']);
    });

    Route::prefix('task')->group(function(){
        Route::get('all', [TaskController::class, 'all']);
        Route::get('search', [TaskController::class, 'search']);
        Route::post('create', [TaskController::class, 'create']);
        Route::delete('{id}', [TaskController::class, 'delete']);
    });
});
