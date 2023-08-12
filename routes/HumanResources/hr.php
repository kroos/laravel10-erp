<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\Leave\HRLeaveController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;
use App\Http\Controllers\HumanResources\HRDept\HRDeptController;
use App\Http\Controllers\HumanResources\HRDept\StaffController;

Route::resources([
    'leave' => HRLeaveController::class,
    'profile' => ProfileController::class,
    'hrdept' => HRDeptController::class,                                // only for links
    'staff' => StaffController::class,
]);
