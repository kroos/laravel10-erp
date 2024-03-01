<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sales\AjaxController;

// Ajax Controller : to CRUD data on the DB
Route::patch('/saleamend/{saleamend}', [AjaxController::class, 'saleamend'])->name('saleamend');
Route::patch('/saleapproved/{saleapproved}', [AjaxController::class, 'saleapproved'])->name('saleapproved');
Route::patch('/salesend/{salesend}', [AjaxController::class, 'salesend'])->name('salesend');




// Ajax DB Controller : only to retrieve data from db
// Route::post('/loginuser', [AjaxDBController::class, 'loginuser'])->name('loginuser');

// Route::get('/login/{login}', [
// 	'as' => 'login.edit',
// 	'uses' => 'Profile\LoginController@edit'
// ]);
