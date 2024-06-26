<?php
// Continuence from routes/web.php
use App\Http\Controllers\HumanResources\AjaxController;
use Illuminate\Support\Facades\Route;

// Ajax Controller : to CRUD data on the DB
Route::patch('/leavecancel/{hrleave}', [AjaxController::class, 'leavecancel'])->name('leavecancel.leavecancel');
Route::patch('/uploaddoc/{hrleave}', [AjaxController::class, 'uploaddoc'])->name('uploaddoc');
Route::patch('/leaverapprove/{hrleaveapprovalbackup}', [AjaxController::class, 'leaverapprove'])->name('leaverapprove.leaverapprove');
Route::patch('/supervisorstatus', [AjaxController::class, 'supervisorstatus'])->name('leavestatus.supervisorstatus');
Route::patch('/hodstatus', [AjaxController::class, 'hodstatus'])->name('leavestatus.hodstatus');
Route::patch('/dirstatus', [AjaxController::class, 'dirstatus'])->name('leavestatus.dirstatus');
Route::patch('/hrstatus', [AjaxController::class, 'hrstatus'])->name('leavestatus.hrstatus');
Route::patch('/deactivatestaff/{staff}', [AjaxController::class, 'deactivatestaff'])->name('deactivatestaff');
Route::delete('/deletecrossbackup/{staff}', [AjaxController::class, 'deletecrossbackup'])->name('deletecrossbackup');
Route::patch('/staffactivate/{staff}', [AjaxController::class, 'staffactivate'])->name('staff.activate');
Route::post('/generateannualleave', [AjaxController::class, 'generateannualleave'])->name('generateannualleave');
Route::post('/generatemcleave', [AjaxController::class, 'generatemcleave'])->name('generatemcleave');
Route::post('/generatematernityleave', [AjaxController::class, 'generatematernityleave'])->name('generatematernityleave');
Route::post('/confirmoutstationattendance', [AjaxController::class, 'confirmoutstationattendance'])->name('confirmoutstationattendance');
