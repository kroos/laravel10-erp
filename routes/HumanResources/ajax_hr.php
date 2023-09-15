<?php
// Continuence from routes/web.php
use App\Http\Controllers\HumanResources\AjaxController;
use App\Http\Controllers\HumanResources\AjaxDBController;
use Illuminate\Support\Facades\Route;

// Ajax Controller : to CRUD data on the DB
Route::patch('/leavecancel/{hrleave}', [AjaxController::class, 'leavecancel'])->name('leavecancel.leavecancel');
Route::patch('/leaverapprove/{hrleaveapprovalbackup}', [AjaxController::class, 'leaverapprove'])->name('leaverapprove.leaverapprove');
Route::patch('/supervisorstatus', [AjaxController::class, 'supervisorstatus'])->name('leavestatus.supervisorstatus');
Route::patch('/hodstatus', [AjaxController::class, 'hodstatus'])->name('leavestatus.hodstatus');
Route::patch('/dirstatus', [AjaxController::class, 'dirstatus'])->name('leavestatus.dirstatus');
Route::patch('/hrstatus', [AjaxController::class, 'hrstatus'])->name('leavestatus.hrstatus');
Route::patch('/deactivatestaff/{staff}', [AjaxController::class, 'deactivatestaff'])->name('deactivatestaff');
Route::delete('/deletecrossbackup/{staff}', [AjaxController::class, 'deletecrossbackup'])->name('deletecrossbackup');


// Ajax DB Controller : only to retrieve data from db
Route::post('/loginuser', [AjaxDBController::class, 'loginuser'])->name('loginuser.loginuser');
Route::post('/leaveType', [AjaxDBController::class, 'leaveType'])->name('leaveType.leaveType');
Route::post('/backupperson', [AjaxDBController::class, 'backupperson'])->name('backupperson.backupperson');
Route::post('/unavailabledate', [AjaxDBController::class, 'unavailabledate'])->name('leavedate.unavailabledate');
Route::post('/timeleave', [AjaxDBController::class, 'timeleave'])->name('leavedate.timeleave');
Route::post('/leavestatus', [AjaxDBController::class, 'leavestatus'])->name('leavestatus.leavestatus');

Route::post('/authorise', [AjaxDBController::class, 'authorise'])->name('authorise.authorise');
Route::post('/branch', [AjaxDBController::class, 'branch'])->name('branch.branch');
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
Route::get('/staffpercentage', [AjaxDBController::class, 'staffpercentage'])->name('staffpercentage');

// Route::get('/login/{login}', [
// 	'as' => 'login.edit',
// 	'uses' => 'Profile\LoginController@edit'
// ]);
