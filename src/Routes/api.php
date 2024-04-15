<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\WorkReport\Controllers\api\WorkReportController;
use Pishgaman\WorkReport\Controllers\api\TaskController;

Route::group(['namespace' => 'Pishgaman\Pishgaman\Http\Controllers\Api','middleware' => [ 'auth:sanctum' ] ], function() {
    Route::prefix('api')->group(function () {
        Route::match(['get','post','put','delete'], 'user/WorkReport', [WorkReportController::class, 'action'])->name('WorkReport_getWorkListApi');    
        Route::match(['get','post','put','delete'], 'user/tasks', [TaskController::class, 'action'])->name('WorkReport_getWorkListApi');    
    });    
});