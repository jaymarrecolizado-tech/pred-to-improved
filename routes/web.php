<?php

use App\Http\Controllers\TravelApprovalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/approvals/{approval}/approve', [TravelApprovalController::class, 'approve'])
    ->name('approvals.approve')
    ->middleware('signed');

Route::get('/approvals/{approval}/reject', [TravelApprovalController::class, 'reject'])
    ->name('approvals.reject')
    ->middleware('signed');

Route::get('/approvals/{approval}/reject-form', [TravelApprovalController::class, 'rejectForm'])
    ->name('approvals.reject.form')
    ->middleware('signed');

Route::post('/approvals/{approval}/reject-form', [TravelApprovalController::class, 'rejectSubmit'])
    ->name('approvals.reject.submit')
    ->middleware('signed');

Route::get('/travel-orders/{order}/preview-pdf', [TravelApprovalController::class, 'previewPdf'])
    ->name('travel-orders.preview-pdf')
    ->middleware(['auth']);
