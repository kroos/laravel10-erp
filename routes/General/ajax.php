<?php
// Continuence from routes/web.php
use App\Http\Controllers\AjaxDBController;
use Illuminate\Support\Facades\Route;

// Ajax DB Controller : only to retrieve data from db
Route::post('/loginuser', [AjaxDBController::class, 'loginuser'])->name('loginuser');
Route::post('/icuser', [AjaxDBController::class, 'icuser'])->name('icuser');
Route::post('/emailuser', [AjaxDBController::class, 'emailuser'])->name('emailuser');
Route::post('/leaveType', [AjaxDBController::class, 'leaveType'])->name('leaveType.leaveType');
Route::post('/backupperson', [AjaxDBController::class, 'backupperson'])->name('backupperson');
Route::post('/unavailabledate', [AjaxDBController::class, 'unavailabledate'])->name('leavedate.unavailabledate');
Route::post('/timeleave', [AjaxDBController::class, 'timeleave'])->name('leavedate.timeleave');
Route::post('/leavestatus', [AjaxDBController::class, 'leavestatus'])->name('leavestatus.leavestatus');

Route::post('/authorise', [AjaxDBController::class, 'authorise'])->name('authorise.authorise');
Route::post('/branch', [AjaxDBController::class, 'branch'])->name('branch.branch');
Route::post('/customer', [AjaxDBController::class, 'customer'])->name('customer.customer');
Route::post('/uom', [AjaxDBController::class, 'uom'])->name('uom.uom');
Route::get('/machine', [AjaxDBController::class, 'machine'])->name('machine.machine');
Route::get('/machineaccessories', [AjaxDBController::class, 'machineaccessories'])->name('machineaccessories.machineaccessories');
// Route::post('/jdescgetitem', [AjaxDBController::class, 'jdescgetitem'])->name('jdescgetitem.jdescgetitem');
Route::post('/category', [AjaxDBController::class, 'category'])->name('category.category');
Route::post('/country', [AjaxDBController::class, 'country'])->name('country.country');
Route::post('/department', [AjaxDBController::class, 'department'])->name('department.department');
Route::post('/division', [AjaxDBController::class, 'division'])->name('division.division');
Route::post('/educationlevel', [AjaxDBController::class, 'educationlevel'])->name('educationlevel.educationlevel');
Route::post('/gender', [AjaxDBController::class, 'gender'])->name('gender.gender');
Route::post('/healthstatus', [AjaxDBController::class, 'healthstatus'])->name('healthstatus.healthstatus');
Route::post('/maritalstatus', [AjaxDBController::class, 'maritalstatus'])->name('maritalstatus.maritalstatus');
Route::post('/religion', [AjaxDBController::class, 'religion'])->name('religion.religion');
Route::post('/race', [AjaxDBController::class, 'race'])->name('race.race');
Route::post('/taxexemptionpercentage', [AjaxDBController::class, 'taxexemptionpercentage'])->name('taxexemptionpercentage.taxexemptionpercentage');
Route::post('/relationship', [AjaxDBController::class, 'relationship'])->name('relationship.relationship');
Route::post('/status', [AjaxDBController::class, 'status'])->name('status.status');
Route::post('/department', [AjaxDBController::class, 'department'])->name('department.department');
Route::post('/restdaygroup', [AjaxDBController::class, 'restdaygroup'])->name('restdaygroup.restdaygroup');
Route::post('/staffcrossbackup', [AjaxDBController::class, 'staffcrossbackup'])->name('staffcrossbackup.staffcrossbackup');
Route::post('/unblockhalfdayleave', [AjaxDBController::class, 'unblockhalfdayleave'])->name('unblockhalfdayleave.unblockhalfdayleave');
Route::post('/leaveevents', [AjaxDBController::class, 'leaveevents'])->name('leaveevents');
Route::post('/division', [AjaxDBController::class, 'division'])->name('division');
Route::post('/staffattendance', [AjaxDBController::class, 'staffattendance'])->name('staffattendance');
Route::post('/staffattendancelist', [AjaxDBController::class, 'staffattendancelist'])->name('staffattendancelist');
Route::post('/staffpercentage', [AjaxDBController::class, 'staffpercentage'])->name('staffpercentage');
Route::post('/yearworkinghourstart', [AjaxDBController::class, 'yearworkinghourstart'])->name('yearworkinghourstart');
Route::post('/yearworkinghourend', [AjaxDBController::class, 'yearworkinghourend'])->name('yearworkinghourend');
Route::post('/hcaldstart', [AjaxDBController::class, 'hcaldstart'])->name('hcaldstart');
Route::post('/hcaldend', [AjaxDBController::class, 'hcaldend'])->name('hcaldend');
Route::post('/staffdaily', [AjaxDBController::class, 'staffdaily'])->name('staffdaily');
Route::post('/samelocationstaff', [AjaxDBController::class, 'samelocationstaff'])->name('samelocationstaff');
Route::post('/overtimerange', [AjaxDBController::class, 'overtimerange'])->name('overtimerange');
Route::post('/branchattendancelist', [AjaxDBController::class, 'branchattendancelist'])->name('branchattendancelist');
Route::post('/outstationattendancestaff', [AjaxDBController::class, 'outstationattendancestaff'])->name('outstationattendancestaff');
Route::post('/outstationattendancelocation', [AjaxDBController::class, 'outstationattendancelocation'])->name('outstationattendancelocation');
Route::post('/staffoutstationduration', [AjaxDBController::class, 'staffoutstationduration'])->name('staffoutstationduration');
Route::post('/attendanceabsentindicator', [AjaxDBController::class, 'attendanceabsentindicator'])->name('attendanceabsentindicator');
Route::post('/week_dates', [AjaxDBController::class, 'week_dates'])->name('week_dates');





















// progress for excel generate
Route::get('/progress', [AjaxDBController::class, 'progress'])->name('progress');

// Route::get('/login/{login}', [
// 	'as' => 'login.edit',
// 	'uses' => 'Profile\LoginController@edit'
// ]);
