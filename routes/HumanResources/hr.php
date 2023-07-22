<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\Leave\LeaveController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;

Route::resources([
    'leave' => LeaveController::class,
    'profile' => ProfileController::class,
]);
