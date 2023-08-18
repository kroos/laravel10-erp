<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\Leave\HRLeaveController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;
use App\Http\Controllers\HumanResources\HRDept\HRDeptController;
use App\Http\Controllers\HumanResources\HRDept\StaffController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceController;

Route::resources([
	'leave' => HRLeaveController::class,
	'profile' => ProfileController::class,
	'hrdept' => HRDeptController::class,								// only for links
	'staff' => StaffController::class,
	'attendance' => AttendanceController::class,
]);


// Route::middleware('auth')->group(function(){
// 	Route::middleware('highMgmtAccess:1|2|3|4|5,NULL')->group(function(){
// 		Route::get('hrdept/index', [HRDeptController::class, 'index']);
// 		Route::get('staff/index', [StaffController::class, 'index']);
// 		Route::get('attendance/index', [AttendanceController::class, 'index']);
// 	});
// });

// Route::middleware('auth')->group(function(){
// 	Route::middleware('highMgmtAccess:1|5,14')->group(function(){
// 		Route::get('hrdept/index', [HRDeptController::class, 'index']);
// 		Route::resources(['staff' => StaffController::class]);
// 		Route::resources(['attendance' => AttendanceController::class, 'index']);
// 	});
// });



// Route::get('staff', [StaffController::class, 'index']);
// Route::post('staff', [StaffController::class, 'store']);
// Route::get('staff/create', [StaffController::class, 'create']);
// Route::get('staff/{staff}', [StaffController::class, 'show']);
// Route::put('staff/{staff}', [StaffController::class, 'update']);
// Route::delete('staff/{staff}', [StaffController::class, 'destroy']);
// Route::get('staff/{staff}/edit', [StaffController::class, 'edit']);
