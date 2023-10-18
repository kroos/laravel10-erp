<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CPS\CPSDeptController;


Route::resources([
	'cpsdept' => CPSDeptController::class,
]);
