<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientOccurrenceController;
use App\Http\Controllers\CommitmentNoteController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\contractProductController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ModalityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOccurrenceController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierOccurrenceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\TenderItemController;
use App\Http\Controllers\TenderOccurrenceController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
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

Route::post('login', [AuthController::class, 'login']);

Route::get('validateToken', [AuthController::class, 'validateToken']);
Route::post('recoverPassword', [UserController::class, 'passwordRecovery']);
Route::post('updatePassword', [UserController::class, 'updatePassword']);

Route::get('validateToken', [AuthController::class, 'validateToken']);

Route::middleware('jwt')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware(AdminMiddleware::class)->group(function () {
    });

    Route::prefix('user')->group(function () {
        Route::get('all', [UserController::class, 'all']);
        Route::get('search', [UserController::class, 'search']);
        Route::get('cards', [UserController::class, 'cards']);
        Route::get('me', [UserController::class, 'getUser']);
        Route::post('create', [UserController::class, 'create']);
        Route::patch('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'delete']);
        Route::post('block/{id}', [UserController::class, 'userBlock']);
    });

    Route::prefix('tender')->group(function () {
        Route::get('all', [TenderController::class, 'all']);
        Route::get('search', [TenderController::class, 'search']);
        Route::get('{id}', [TenderController::class, 'getById']);
        Route::post('create', [TenderController::class, 'create']);

        Route::delete('attachment/{attachmentId}', [TenderController::class, 'deleteAttachment']);
        Route::delete('item/{itemId}', [TenderController::class, 'deleteItem']);
        Route::delete('task/{taskId}', [TenderController::class, 'deleteTask']);
        Route::patch('convert-to-contract/{tender_id}', [TenderController::class, 'convertToContract']);
        Route::patch('{tender_id}/status', [TenderController::class, 'updateStatus']);

        Route::patch('{id}', [TenderController::class, 'update']);
        Route::delete('{id}', [TenderController::class, 'delete']);
    });

    Route::prefix('tender-item')->group(function () {
        Route::get('search', [TenderItemController::class, 'search']);
        Route::get('{id}', [TenderItemController::class, 'getById']);
        Route::post('create', [TenderItemController::class, 'create']);
        Route::patch('{id}', [TenderItemController::class, 'update']);
        Route::delete('{id}', [TenderItemController::class, 'delete']);
    });

    Route::prefix('modality')->group(function () {
        Route::get('all', [ModalityController::class, 'all']);
        Route::get('search', [ModalityController::class, 'search']);
        Route::post('create', [ModalityController::class, 'create']);
        Route::patch('{id}', [ModalityController::class, 'update']);
        Route::delete('{id}', [ModalityController::class, 'delete']);
    });

    Route::prefix('client')->group(function () {
        Route::get('all', [ClientController::class, 'all']);
        Route::get('search', [ClientController::class, 'search']);
        Route::post('create', [ClientController::class, 'create']);
        Route::patch('{id}', [ClientController::class, 'update']);
        Route::delete('attachment/{attachmentId}', [ClientController::class, 'deleteAttachment']);
        Route::delete('{id}', [ClientController::class, 'delete']);
    });

    Route::prefix('supplier')->group(function () {
        Route::get('all', [SupplierController::class, 'all']);
        Route::get('search', [SupplierController::class, 'search']);
        Route::post('create', [SupplierController::class, 'create']);
        Route::patch('{id}', [SupplierController::class, 'update']);
        Route::delete('{id}', [SupplierController::class, 'delete']);
    });

    Route::prefix('tender-occurrence')->group(function () {
        Route::get('all', [TenderOccurrenceController::class, 'all']);
        Route::get('search', [TenderOccurrenceController::class, 'search']);
        Route::post('create', [TenderOccurrenceController::class, 'create']);
        Route::patch('{id}', [TenderOccurrenceController::class, 'update']);
        Route::delete('{id}', [TenderOccurrenceController::class, 'delete']);
    });

    Route::prefix('product')->group(function () {
        Route::get('all', [ProductController::class, 'all']);
        Route::get('search', [ProductController::class, 'search']);
        Route::get('historical', [ProductController::class, 'historical']);
        Route::post('create', [ProductController::class, 'create']);
        Route::patch('{id}', [ProductController::class, 'update']);
        Route::delete('attachment/{attachmentId}', [ProductController::class, 'deleteAttachment']);
        Route::delete('{id}', [ProductController::class, 'delete']);
    });

    Route::prefix('client-occurrence')->group(function () {
        Route::get('search', [ClientOccurrenceController::class, 'search']);
        Route::post('create', [ClientOccurrenceController::class, 'create']);
    });

    Route::prefix('product-occurrence')->group(function () {
        Route::get('search', [ProductOccurrenceController::class, 'search']);
        Route::post('create', [ProductOccurrenceController::class, 'create']);
    });

    Route::prefix('supplier-occurrence')->group(function () {
        Route::get('search', [SupplierOccurrenceController::class, 'search']);
        Route::post('create', [SupplierOccurrenceController::class, 'create']);
    });

    Route::prefix('status')->group(function () {
        Route::get('all', [StatusController::class, 'all']);
        Route::get('search', [StatusController::class, 'search']);
        Route::post('create', [StatusController::class, 'create']);
        Route::patch('{id}', [StatusController::class, 'update']);
        Route::delete('{id}', [StatusController::class, 'delete']);
    });

    Route::prefix('notification')->group(function () {
        Route::get('all', [NotificationController::class, 'all']);
        Route::get('search', [NotificationController::class, 'search']);
        Route::post('create', [NotificationController::class, 'create']);
        Route::patch('{id}', [NotificationController::class, 'update']);
        Route::delete('{id}', [NotificationController::class, 'delete']);
    });

    Route::prefix('log')->group(function () {
        Route::get('all', [LogController::class, 'all']);
        Route::get('search', [LogController::class, 'search']);
    });

    Route::prefix('task')->group(function () {
        Route::get('all', [TaskController::class, 'all']);
        Route::get('search', [TaskController::class, 'search']);
        Route::post('create', [TaskController::class, 'create']);
        Route::patch('{id}', [TaskController::class, 'update']);
        Route::delete('{id}', [TaskController::class, 'delete']);
    });

    Route::prefix('commitment-note')->group(function () {
        Route::get('all', [CommitmentNoteController::class, 'all']);
        Route::get('search', [CommitmentNoteController::class, 'search']);
        Route::post('create', [CommitmentNoteController::class, 'create']);
        Route::patch('{id}', [CommitmentNoteController::class, 'update']);
        Route::delete('{id}', [CommitmentNoteController::class, 'delete']);
        Route::delete('product/{commitment_product_id}', [CommitmentNoteController::class, 'deleteProduct']);
    });

    Route::prefix('contract')->group(function () {
        Route::get('all', [ContractController::class, 'all']);
        Route::get('search', [ContractController::class, 'search']);
        Route::post('create', [ContractController::class, 'create']);
        Route::patch('{id}', [ContractController::class, 'update']);
        Route::delete('{id}', [ContractController::class, 'delete']);

        Route::post('{contractId}/payment', [ContractController::class, 'createPayment']);
        Route::delete('payment/{paymentId}', [ContractController::class, 'deletePayment']);
        Route::delete('product/{contractProductId}', [ContractController::class, 'deleteContractProduct']);
        Route::delete('attachment/{attachmentId}', [ContractController::class, 'deleteAttachment']);
    });

    Route::prefix('contract-product')->group(function () {
        Route::get('search', [contractProductController::class, 'search']);
        Route::get('{id}', [contractProductController::class, 'getById']);
        Route::post('create', [contractProductController::class, 'create']);
        Route::patch('{id}', [contractProductController::class, 'update']);
        Route::delete('{id}', [contractProductController::class, 'delete']);
    });
});
