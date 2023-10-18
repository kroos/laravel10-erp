<?php
// Continuence from routes/web.php
use App\Http\Controllers\CPS\AjaxController;
use App\Http\Controllers\CPS\AjaxDBController;
use Illuminate\Support\Facades\Route;

// Ajax Controller : to CRUD data on the DB
// Route::patch('/leavecancel/{hrleave}', [AjaxController::class, 'leavecancel'])->name('leavecancel.leavecancel');




// Ajax DB Controller : only to retrieve data from db
// Route::post('/loginuser', [AjaxDBController::class, 'loginuser'])->name('loginuser');

// Route::get('/login/{login}', [
// 	'as' => 'login.edit',
// 	'uses' => 'Profile\LoginController@edit'
// ]);
