<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Costing\CostingDeptController;

Route::get('/costingdept', [CostingDeptController::class, 'index'])->name('costingdept.index');

// Route::resources([
// 	'costingdept' => CostingDeptController::class,
// ]);
