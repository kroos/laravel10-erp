<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\sales\SalesDeptController;

Route::resources([
	'salesdept' => SalesDeptController::class,
]);
