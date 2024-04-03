<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\WorkReport\Controllers\web\WorkReportController;
use Pishgaman\WorkReport\Controllers\web\TaskController;

Route::get('/user/work/report', [WorkReportController::class, 'index'])->name('WorkReport_getWorkList')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/user/tasks', [TaskController::class, 'userTask'])->name('WorkReport_userTask')->middleware(['auth','CheckMenuAccess','web']);
