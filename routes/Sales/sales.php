<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sales\SalesDeptController;
use App\Http\Controllers\Sales\SalesController;

Route::get('/salesdept', [SalesDeptController::class, 'index'])->name('salesdept.index');

Route::resources([
	'sales' => SalesController::class,
]);

