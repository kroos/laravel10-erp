<?php
// Continuence from routes/web.php
use App\Http\Controllers\HumanResources\AjaxController;
use Illuminate\Support\Facades\Route;

// Ajax Controller
Route::patch('/leavecancel/{hrleave}', [AjaxController::class, 'update'])->name('leavecancel.update');

// Route::get('/login/{login}', [
// 	'as' => 'login.edit',
// 	'uses' => 'Profile\LoginController@edit'
// ]);
