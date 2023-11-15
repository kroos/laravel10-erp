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
use App\Http\Controllers\HumanResources\HRDept\OvertimeController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceUploadController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceDailyReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceExcelReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceReportPDFController;


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
	'overtime' => OvertimeController::class,
]);

Route::get('/excelreport', [AttendanceExcelReportController::class, 'index'])->name('excelreport.index');
Route::get('/excelreport/create', [AttendanceExcelReportController::class, 'create'])->name('excelreport.create');
Route::post('/excelreport', [AttendanceExcelReportController::class, 'store'])->name('excelreport.store');

Route::get('/attendancedailyreport', [AttendanceDailyReportController::class, 'index'])->name('attendancedailyreport.index');
Route::get('/attendancereport', [AttendanceReportController::class, 'index'])->name('attendancereport.index');
Route::post('/attendancereport', [AttendanceReportController::class, 'create'])->name('attendancereport.create');
Route::get('/attendanceupload', [AttendanceUploadController::class, 'create'])->name('attendanceupload.create');
Route::post('/attendanceupload', [AttendanceUploadController::class, 'store'])->name('attendanceupload.store');

Route::get('/attendancereportpdf/store', [AttendanceReportPDFController::class, 'store'])->name('attendancereportpdf.store');


