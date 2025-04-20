<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintJobController;
use App\Http\Controllers\PrintStatusController;
use App\Http\Controllers\API\PrintJobController as APIPrintJobController;

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

// User Print Jobs API Routes
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // View and manage print jobs
    Route::get('/print-jobs', [APIPrintJobController::class, 'managePrintJobs']);
    Route::get('/print-jobs/{id}', [APIPrintJobController::class, 'getPrintJobDetails']);
    Route::put('/print-jobs/{id}/status', [APIPrintJobController::class, 'updatePrintJobStatus']);

    // Document validation and issues
    Route::post('/documents/{documentId}/validate', [APIPrintJobController::class, 'validateDocument']);
    Route::put('/documents/{documentId}/issues', [APIPrintJobController::class, 'manageDocumentIssues']);
    Route::get('/documents/{documentId}/issues', [APIPrintJobController::class, 'getDocumentIssues']);
});

// Print Jobs API Routes
Route::prefix('print-jobs')->group(function () {
    Route::get('/', [PrintJobController::class, 'index']);
    Route::post('/', [PrintJobController::class, 'store']);
    Route::post('/upload', [PrintJobController::class, 'uploadFile']);
    Route::get('/{printJob}', [PrintJobController::class, 'show']);
    Route::put('/{printJob}', [PrintJobController::class, 'update']);
    Route::delete('/{printJob}', [PrintJobController::class, 'destroy']);
});

// Print Status API Routes
Route::prefix('print-status')->group(function () {
    // Get all possible status values
    Route::get('/options', [PrintStatusController::class, 'getStatusOptions']);

    // Get status history
    Route::get('/job/{printJob}/history', [PrintStatusController::class, 'getJobStatusHistory']);
    Route::get('/batch/{printBatch}/history', [PrintStatusController::class, 'getBatchStatusHistory']);

    // Get status summaries
    Route::get('/jobs/summary', [PrintStatusController::class, 'getJobsStatusSummary']);
    Route::get('/batches/summary', [PrintStatusController::class, 'getBatchesStatusSummary']);

    // Get items by status
    Route::get('/jobs/by-status', [PrintStatusController::class, 'getJobsByStatus']);
    Route::get('/batches/by-status', [PrintStatusController::class, 'getBatchesByStatus']);

    // Get recent changes and statistics
    Route::get('/recent-changes', [PrintStatusController::class, 'getRecentStatusChanges']);
    Route::get('/statistics', [PrintStatusController::class, 'getStatusStatistics']);
});
