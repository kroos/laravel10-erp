<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sales\SalesDeptController;
use App\Http\Controllers\Sales\SalesController;
use App\Http\Controllers\Sales\SalesJobDescriptionController;
use App\Http\Controllers\Sales\SalesCustomerController;

Route::get('/salesdept', [SalesDeptController::class, 'index'])->name('salesdept.index');

Route::resources([
	'sale' => SalesController::class,
	'salesjobdescription' => SalesJobDescriptionController::class,
	'salescustomer' => SalesCustomerController::class,
]);

