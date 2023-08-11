<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\Leave\HRLeaveController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;
use App\Http\Controllers\HumanResources\HRDept\HRDeptController;

Route::resources([
    'leave' => HRLeaveController::class,
    'profile' => ProfileController::class,
    'hrdept' => HRDeptController::class,
]);
