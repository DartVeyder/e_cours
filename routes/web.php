<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupExportController;

Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/export/group/{group}/excel', [GroupExportController::class, 'exportExcel'])
    ->middleware('auth')
    ->name('export.group.excel');

Route::get('/export/analytics/excel', [\App\Http\Controllers\AnalyticsExportController::class, 'export'])
    ->middleware('auth')
    ->name('export.analytics.excel');

// API endpoint for retrieving students list along with chosen subjects
Route::get('/api/students-subjects', [\App\Http\Controllers\Api\StudentSubjectController::class, 'index']);

