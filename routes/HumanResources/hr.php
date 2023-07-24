<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\Leave\HRLeaveController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;

Route::resources([
    'leave' => HRLeaveController::class,
    'profile' => ProfileController::class,
]);
