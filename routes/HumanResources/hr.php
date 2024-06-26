<?php
// Continuence from routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HumanResources\HRDept\LeaveController;
use App\Http\Controllers\HumanResources\HRDept\StaffController;
use App\Http\Controllers\HumanResources\HRDept\AbsentController;
use App\Http\Controllers\HumanResources\HRDept\HRDeptController;
use App\Http\Controllers\HumanResources\HRDept\SpouseController;
use App\Http\Controllers\HumanResources\Leave\HRLeaveController;
use App\Http\Controllers\HumanResources\HRDept\MCLeaveController;
use App\Http\Controllers\HumanResources\HRDept\ChildrenController;
use App\Http\Controllers\HumanResources\HRDept\OvertimeController;
use App\Http\Controllers\HumanResources\Profile\ProfileController;
use App\Http\Controllers\HumanResources\HRDept\HRMCLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRSettingController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceController;
use App\Http\Controllers\HumanResources\HRDept\DisciplineController;
use App\Http\Controllers\HumanResources\HRDept\HRUPLLeaveController;
use App\Http\Controllers\HumanResources\HRDept\OutstationController;
use App\Http\Controllers\HumanResources\HRDept\AnnualLeaveController;
use App\Http\Controllers\HumanResources\HRDept\WorkingHourController;
use App\Http\Controllers\HumanResources\HRDept\HRMCUPLLeaveController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalFormController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalListController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalMarkController;
use App\Http\Controllers\HumanResources\HRDept\HRAnnualLeaveController;
use App\Http\Controllers\HumanResources\HRDept\MaternityLeaveController;
use App\Http\Controllers\HumanResources\HRDept\OvertimeReportController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalApointController;
use App\Http\Controllers\HumanResources\HRDept\HolidayCalendarController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalSettingController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceRemarkController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceUploadController;
use App\Http\Controllers\HumanResources\HRDept\EmergencyContactController;
use App\Http\Controllers\HumanResources\HRDept\HRMaternityLeaveController;
use App\Http\Controllers\HumanResources\HRDept\ReplacementLeaveController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalHRController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalHODController;
use App\Http\Controllers\HumanResources\HRDept\HRReplacementLeaveController;
use App\Http\Controllers\HumanResources\HRDept\OutstationDurationController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceReportPDFController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalExcelReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceDailyReportController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceExcelReportController;
use App\Http\Controllers\HumanResources\HRDept\HROutstationAttendanceController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalDirectorController;
use App\Http\Controllers\HumanResources\HRDept\AppraisalFormMoreFunctionController;
use App\Http\Controllers\HumanResources\HRDept\AttendanceAbsentIndicatorController;
use App\Http\Controllers\HumanResources\HRDept\ConditionalIncentiveStaffController;
use App\Http\Controllers\HumanResources\HRDept\HRLeaveApprovalSupervisorController;
use App\Http\Controllers\HumanResources\HRDept\ConditionalIncentiveCategoryController;
use App\Http\Controllers\HumanResources\HRDept\ConditionalIncentiveCategoryItemController;
use App\Http\Controllers\HumanResources\HRDept\ConditionalIncentiveStaffCheckingController;
use App\Http\Controllers\HumanResources\HRDept\ConditionalIncentiveStaffCheckingReportController;
use App\Http\Controllers\HumanResources\OutstationAttendance\OutstationAttendanceController;
use App\Http\Controllers\HumanResources\HRDept\AttendancePayslipExcelReportSettingController;



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
	'absent' => AbsentController::class,
	'outstationattendance' => OutstationAttendanceController::class,
	'hroutstationattendance' => HROutstationAttendanceController::class,
	'appraisalexcelreport' => AppraisalExcelReportController::class,
	'attendanceremark' => AttendanceRemarkController::class,
	'appraisalform' => AppraisalFormController::class,
	'appraisalapoint' => AppraisalApointController::class,
	'appraisallist' => AppraisalListController::class,
	'appraisalmark' => AppraisalMarkController::class,
	'cicategory' => ConditionalIncentiveCategoryController::class,
	'cicategoryitem' => ConditionalIncentiveCategoryItemController::class,
	'cicategorystaff' => ConditionalIncentiveStaffController::class,
	'cicategorystaffcheck' => ConditionalIncentiveStaffCheckingController::class,
]);

Route::get('/leavereject', [LeaveController::class, 'reject'])->name('hrleave.reject');
Route::get('/leavecancel', [LeaveController::class, 'cancel'])->name('hrleave.cancel');

// Route::get('/excelreport', [AttendanceExcelReportController::class, 'index'])->name('excelreport.index');
Route::get('/excelreport/create', [AttendanceExcelReportController::class, 'create'])->name('excelreport.create');
Route::post('/excelreport', [AttendanceExcelReportController::class, 'store'])->name('excelreport.store');

Route::get('/attendancereport/create', [AttendanceReportController::class, 'create'])->name('attendancereport.create');
Route::get('/attendancereport/store', [AttendanceReportController::class, 'store'])->name('attendancereport.store');
Route::get('/attendancereportpdf/store', [AttendanceReportPDFController::class, 'store'])->name('attendancereportpdf.store');

Route::get('/attendanceupload/create', [AttendanceUploadController::class, 'create'])->name('attendanceupload.create');
Route::post('/attendanceupload', [AttendanceUploadController::class, 'store'])->name('attendanceupload.store');

Route::get('/overtimereport', [OvertimeReportController::class, 'index'])->name('overtimereport.index');
Route::post('/overtimereport', [OvertimeReportController::class, 'index'])->name('overtimereport.index');
Route::get('/overtimereport/print', [OvertimeReportController::class, 'print'])->name('overtimereport.print');

Route::get('/attendancedailyreport', [AttendanceDailyReportController::class, 'index'])->name('attendancedailyreport.index');
Route::post('/attendancedailyreport', [AttendanceDailyReportController::class, 'index'])->name('attendancedailyreport.index');
Route::get('/attendancedailyreport/print', [AttendanceDailyReportController::class, 'print'])->name('attendancedailyreport.print');

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

Route::get('/appraisalform/create/{id}', [AppraisalFormController::class, 'create'])->name('appraisalform.create');

Route::get('/staff/show/{staff}', [StaffController::class, 'show'])->name('staff.show');
Route::post('/staff/show/{staff}', [StaffController::class, 'show'])->name('staff.show');

Route::get('/profile/show/{profile}', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile/show/{profile}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/outstationduration', [OutstationDurationController::class, 'index'])->name('outstationduration.index');

Route::get('/appraisalformpdf/print', [AppraisalFormMoreFunctionController::class, 'print'])->name('appraisalformpdf.print');
Route::get('/appraisalformduplicate/store', [AppraisalFormMoreFunctionController::class, 'store'])->name('appraisalformduplicate.store');

Route::get('/appraisalsetting/create', [AppraisalSettingController::class, 'create'])->name('appraisalsetting.create');
Route::patch('/appraisalsetting/update/{appraisalsetting}', [AppraisalSettingController::class, 'update'])->name('appraisalsetting.update');

Route::get('/attendancepayslipexcelsetting/create', [AttendancePayslipExcelReportSettingController::class, 'create'])->name('attendancepayslipexcelsetting.create');
Route::patch('/attendancepayslipexcelsetting/update/{attendancepayslipexcelsetting}', [AttendancePayslipExcelReportSettingController::class, 'update'])->name('attendancepayslipexcelsetting.update');

Route::get('/attendanceabsentindicator/index', [AttendanceAbsentIndicatorController::class, 'index'])->name('attendanceabsentindicator.index');

Route::get('/appraisalmark/create/{id}', [AppraisalMarkController::class, 'create'])->name('appraisalmark.create');

Route::get('/cicategorystaffcheckreport/create', [ConditionalIncentiveStaffCheckingReportController::class, 'create'])->name('cicategorystaffcheckreport.create');
Route::post('/cicategorystaffcheckreport/store', [ConditionalIncentiveStaffCheckingReportController::class, 'store'])->name('cicategorystaffcheckreport.store');
