<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\Leave\HRLeaveController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;
use App\Http\Controllers\HumanResources\HRDept\HRDeptController;
use App\Http\Controllers\HumanResources\HRDept\StaffController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceController;
use App\Http\Controllers\HumanResources\HRDept\LeaveController;
use App\Http\Controllers\HumanResources\HRDept\SpouseController;
use App\Http\Controllers\HumanResources\HRDept\ChildrenController;
use App\Http\Controllers\HumanResources\HRDept\EmergencyContactController;

Route::resources([
	'leave' => HRLeaveController::class,
	'profile' => ProfileController::class,
	'hrdept' => HRDeptController::class,								// only for links
	'staff' => StaffController::class,
	'attendance' => AttendanceController::class,
	'hrleave' => LeaveController::class,
	'spouse' => SpouseController::class,
	'children' => ChildrenController::class,
	'emergencycontact' => EmergencyContactController::class,
]);

