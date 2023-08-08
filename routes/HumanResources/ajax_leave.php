<?php
// Continuence from routes/web.php
use App\Http\Controllers\HumanResources\AjaxController;
use Illuminate\Support\Facades\Route;

// Ajax Controller
Route::patch('/leavecancel/{hrleave}', [AjaxController::class, 'leavecancel'])->name('leavecancel.leavecancel');
Route::post('/leaveType', [AjaxController::class, 'leaveType'])->name('leaveType.leaveType');
Route::post('/backupperson', [AjaxController::class, 'backupperson'])->name('backupperson.backupperson');
Route::post('/unavailabledate', [AjaxController::class, 'unavailabledate'])->name('leavedate.unavailabledate');
Route::post('/timeleave', [AjaxController::class, 'timeleave'])->name('leavedate.timeleave');
Route::patch('/leaverapprove/{hrleaveapprovalbackup}', [AjaxController::class, 'leaverapprove'])->name('leaverapprove.leaverapprove');

// Route::get('/login/{login}', [
// 	'as' => 'login.edit',
// 	'uses' => 'Profile\LoginController@edit'
// ]);
