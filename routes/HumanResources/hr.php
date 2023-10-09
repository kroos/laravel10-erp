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
use App\Http\Controllers\HumanResources\HRDept\AttendanceReportController;
use App\Http\Controllers\HumanResources\HRDept\ReplacementLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRSettingController;
use App\Http\Controllers\HumanResources\HRDept\WorkingHourController;
use App\Http\Controllers\HumanResources\HRDept\HolidayCalendarController;
use App\Http\Controllers\HumanResources\HRDept\DisciplineController;
use App\Http\Controllers\HumanResources\HRDept\OutstationController;
use App\Http\Controllers\HumanResources\HRDept\AnnualLeaveController;
use App\Http\Controllers\HumanResources\HRDept\MCLeaveController;
use App\Http\Controllers\HumanResources\HRDept\MaternityLeaveController;


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
	'rleave' => ReplacementLeaveController::class,
	'hrsetting' => HRSettingController::class,
	'workinghour' => WorkingHourController::class,
	'holidaycalendar' => HolidayCalendarController::class,
	'discipline' => DisciplineController::class,
	'discipline' => DisciplineController::class,
	'outstation' => OutstationController::class,
	'annualleave' => AnnualLeaveController::class,
	'mcleave' => MCLeaveController::class,
	'maternityleave' => MaternityLeaveController::class,
]);

Route::get('/attendancereport', [AttendanceReportController::class, 'index'])->name('attendancereport.index');
Route::post('/attendancereport', [AttendanceReportController::class, 'create'])->name('attendancereport.create');



// testing
Route::get('/rleave', [ReplacementLeaveController::class, 'replacement_ajax'])->name('rleave.replacement_ajax');
