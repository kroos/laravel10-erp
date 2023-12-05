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
use App\Http\Controllers\HumanResources\HRDept\HRAnnualLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRMCLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRMaternityLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRReplacementLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRUPLLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRMCUPLLeaveController;
use App\Http\Controllers\HumanResources\HRDept\OvertimeController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceUploadController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceDailyReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceExcelReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceReportPDFController;
use App\Http\Controllers\HumanResources\HRDept\OvertimeReportController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalSupervisorController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalHODController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalDirectorController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalHRController;


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
	'hrannualleave' => HRAnnualLeaveController::class,
	'hrmcleave' => HRMCLeaveController::class,
	'hrmaternityleave' => HRMaternityLeaveController::class,
	'hrreplacementleave' => HRReplacementLeaveController::class,
	'hruplleave' => HRUPLLeaveController::class,
	'hrmcuplleave' => HRMCUPLLeaveController::class,
	'overtime' => OvertimeController::class,
	'leaveapprovalsupervisor' => HRLeaveApprovalSupervisorController::class,
	'leaveapprovalhod' => HRLeaveApprovalHODController::class,
	'leaveapprovaldirector' => HRLeaveApprovalDirectorController::class,
	'leaveapprovalhr' => HRLeaveApprovalHRController::class,
]);

Route::get('/leavereject', [LeaveController::class, 'reject'])->name('hrleave.reject');
Route::get('/leavecancel', [LeaveController::class, 'cancel'])->name('hrleave.cancel');

Route::get('/excelreport', [AttendanceExcelReportController::class, 'index'])->name('excelreport.index');
Route::get('/excelreport/create', [AttendanceExcelReportController::class, 'create'])->name('excelreport.create');
Route::post('/excelreport', [AttendanceExcelReportController::class, 'store'])->name('excelreport.store');

Route::get('/attendancereport/create', [AttendanceReportController::class, 'create'])->name('attendancereport.create');
Route::get('/attendancereport/store', [AttendanceReportController::class, 'store'])->name('attendancereport.store');
Route::get('/attendancereportpdf/store', [AttendanceReportPDFController::class, 'store'])->name('attendancereportpdf.store');

Route::get('/attendanceupload', [AttendanceUploadController::class, 'create'])->name('attendanceupload.create');
Route::post('/attendanceupload', [AttendanceUploadController::class, 'store'])->name('attendanceupload.store');

Route::get('/overtimereport', [OvertimeReportController::class, 'index'])->name('overtimereport.index');
Route::post('/overtimereport', [OvertimeReportController::class, 'index'])->name('overtimereport.index');
Route::get('/overtimereport/print', [OvertimeReportController::class, 'print'])->name('overtimereport.print');

Route::get('/attendancedailyreport', [AttendanceDailyReportController::class, 'index'])->name('attendancedailyreport.index');
Route::post('/attendancedailyreport', [AttendanceDailyReportController::class, 'index'])->name('attendancedailyreport.index');

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');