<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load model
use App\Models\Setting;

use App\Models\Staff;
use App\Models\Login;
use App\Models\Customer;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HROutstationAttendance;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveEntitlement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\OptStatus;
use App\Models\HumanResources\DepartmentPivot;
use App\Models\HumanResources\HROvertime;

use App\Models\HumanResources\OptAuthorise;
use App\Models\HumanResources\OptBranch;
use App\Models\HumanResources\OptCategory;
use App\Models\HumanResources\OptCountry;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptDepartment;
use App\Models\HumanResources\OptDivision;
use App\Models\HumanResources\OptEducationLevel;
use App\Models\HumanResources\OptGender;
use App\Models\HumanResources\OptHealthStatus;
use App\Models\HumanResources\OptLeaveStatus;
use App\Models\HumanResources\OptLeaveType;
use App\Models\HumanResources\OptMaritalStatus;
use App\Models\HumanResources\OptRace;
use App\Models\HumanResources\OptRelationship;
use App\Models\HumanResources\OptReligion;
use App\Models\HumanResources\OptRestdayGroup;
use App\Models\HumanResources\OptTaxExemptionPercentage;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\OptWorkingHour;
use App\Models\HumanResources\HROvertimeRange;
use App\Models\JobBatch;

use Illuminate\Database\Eloquent\Builder;

// load helper
use App\Helpers\UnavailableDateTime;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load batch and queue
use Illuminate\Support\Facades\Bus;
// use Illuminate\Bus\Batch;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class AjaxDBController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	// compared username
	public function loginuser(Request $request): JsonResponse
	{
		$valid = true;
		$log = \App\Models\Login::all();
		foreach($log as $k) {
			if($k->username == $request->username) {
				$valid = false;
			}
		}
		return response()->json([
			'valid' => $valid,
		]);
	}

	public function icuser(Request $request): JsonResponse
	{
		$valid = true;
		$log = \App\Models\Staff::all();
		foreach($log as $k) {
			if($k->ic == $request->ic) {
				$valid = false;
			}
		}
		return response()->json([
			'valid' => $valid,
		]);
	}

	public function emailuser(Request $request): JsonResponse
	{
		$valid = true;
		$log = \App\Models\Staff::all();
		foreach($log as $k) {
			if($k->email == $request->email) {
				$valid = false;
			}
		}
		return response()->json([
			'valid' => $valid,
		]);
	}

	// get types of leave according to user
	public function leaveType(Request $request): JsonResponse
	{
		$user = Staff::find($request->id);
		// tahun lepas
		$pastyear = now()->subYear()->year;
		// tahun sekarang ni
		$year = now()->year;
		$nextyear = Carbon::parse(now()->addYear())->year;
		// dd(Setting::find(6)->active, $year, $nextyear);

		// group entitlement by year
		for ($i = ((Setting::find(7)->active != 1)?$pastyear:$year); $i <= ((Setting::find(6)->active != 1)?$nextyear:$year); ++$i) {

			// checking for annual leave, mc, nrl and maternity
			// hati-hati dgn yg ni sbb melibatkan masa
			$leaveAL =  $user->hasmanyleaveannual()->where('year', $i)->first();
			$leaveMC =  $user->hasmanyleavemc()->where('year', $i)->first();
			$leaveMa =  $user->hasmanyleavematernity()->where('year', $i)->first();
			// cari kalau ada replacement leave
			$oi = $user->hasmanyleavereplacement()?->where('leave_balance', '<>', 0)->whereYear('date_start', $i)->get();

			// dd($oi?->sum('leave_balance'));

			if(Setting::where('id', 3)->first()->active == 1){																		// special unpaid leave activated
				if($user->gender_id == 1){																							// laki
					if($oi?->sum('leave_balance') < 0.5){																			// laki | no nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// laki | no nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | no nrl | no al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [3,6,9,11,12])->get()->sortBy('sorting');
							} else {																								// laki | no nrl | no al | mc
								$er[$i] = OptLeaveType::whereIn('id', [2,3,6,9,12])->get()->sortBy('sorting');
							}
						} else {																									// laki | no nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | no nrl | al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [1,5,9,11,12])->get()->sortBy('sorting');
							} else {																								// laki | no nrl | al | mc
								$er[$i] = OptLeaveType::whereIn('id', [1,2,5,9,12])->get()->sortBy('sorting');
							}
						}
					} else {																										// laki | nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// laki | nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | nrl | no al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [3,4,6,9,10,11,12])->get()->sortBy('sorting');
							} else {																								// laki | nrl | no al | mc
								$er[$i] = OptLeaveType::whereIn('id', [2,3,4,6,9,10,12])->get()->sortBy('sorting');
							}
						} else {																									// laki | nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | nrl | al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [1,4,5,9,10,11,12])->get()->sortBy('sorting');
							} else {																								// laki | nrl | al | mc
								$er[$i] = OptLeaveType::whereIn('id', [1,2,4,5,9,10,12])->get()->sortBy('sorting');
							}
						}
					}
				} else {																											// pempuan
					if($oi?->sum('leave_balance') < 0.5){																			// pempuan | no nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// pempuan | no nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | no nrl | no al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | no al |  no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,6,9,11,12])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | no al |  no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,6,7,9,11,12])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | no nrl | no al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | no al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,6,9,12])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | no al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,6,7,9,12])->get()->sortBy('sorting');
								}
							}
						} else {																									// pempuan | no nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | no nrl | al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,5,9,11,12])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,5,7,9,11,12])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | no nrl | al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,5,9,12])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,5,7,9,12])->get()->sortBy('sorting');
								}
							}
						}
					} else {																										// pempuan | nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// pempuan | nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | nrl | no al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,4,6,7,9,10,11,12])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | no al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,4,6,7,9,10,11,12])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | nrl | no al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,4,6,9,10,12])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | no al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,4,6,7,9,10,12])->get()->sortBy('sorting');
								}
							}
						} else {																									// pempuan | nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | nrl | al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,4,5,9,10,11,12])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,4,5,7,9,10,11,12])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | nrl | al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,4,5,9,10,12])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,4,5,7,9,10,12])->get()->sortBy('sorting');
								}
							}
						}
					}
				}
			} else {																												// special unpaid leave deactivated
				if($user->gender_id == 1){																							// laki
					if($oi?->sum('leave_balance') < 0.5){																			// laki | no nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// laki | no nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | no nrl | no al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [3,6,9,11])->get()->sortBy('sorting');
							} else {																								// laki | no nrl | no al | mc
								$er[$i] = OptLeaveType::whereIn('id', [2,3,6,9])->get()->sortBy('sorting');
							}
						} else {																									// laki | no nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | no nrl | al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [1,5,9,11])->get()->sortBy('sorting');
							} else {																								// laki | no nrl | al | mc
								$er[$i] = OptLeaveType::whereIn('id', [1,2,5,9])->get()->sortBy('sorting');
							}
						}
					} else {																										// laki | nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// laki | nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | nrl | no al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [3,4,6,9,10,11])->get()->sortBy('sorting');
							} else {																								// laki | nrl | no al | mc
								$er[$i] = OptLeaveType::whereIn('id', [2,3,4,6,9,10])->get()->sortBy('sorting');
							}
						} else {																									// laki | nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// laki | nrl | al | no mc
								$er[$i] = OptLeaveType::whereIn('id', [1,4,5,9,10,11])->get()->sortBy('sorting');
							} else {																								// laki | nrl | al | mc
								$er[$i] = OptLeaveType::whereIn('id', [1,2,4,5,9,10])->get()->sortBy('sorting');
							}
						}
					}
				} else {																											// pempuan
					if($oi?->sum('leave_balance') < 0.5){																			// pempuan | no nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// pempuan | no nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | no nrl | no al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,6,9,11])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,6,7,9,11])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | no nrl | no al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | no al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,6,9])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | no al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,6,7,9])->get()->sortBy('sorting');
								}
							}
						} else {																									// pempuan | no nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | no nrl | al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,5,7,9,11])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,5,7,9,11])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | no nrl | al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,5,9])->get()->sortBy('sorting');
								} else {																							// pempuan | no nrl | al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,5,7,9])->get()->sortBy('sorting');
								}
							}
						}
					} else {																										// pempuan | nrl
						if($leaveAL?->annual_leave_balance < 0.5){																	// pempuan | nrl | no al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | nrl | no al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,4,6,9,10,11])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | no al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [3,4,6,7,9,10,11])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | nrl | no al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,4,6,9,10])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | no al | mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [2,3,4,6,7,9,10])->get()->sortBy('sorting');
								}
							}
						} else {																									// pempuan | nrl | al
							if($leaveMC?->mc_leave_balance < 0.5){																	// pempuan | nrl | al | no mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,4,5,9,10,11])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,4,5,7,9,10,11])->get()->sortBy('sorting');
								}
							} else {																								// pempuan | nrl | al | mc
								if($leaveMa?->maternity_leave_balance < 0.5){														// pempuan | nrl | al | no mc | no maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,4,5,9,10])->get()->sortBy('sorting');
								} else {																							// pempuan | nrl | al | no mc | maternity
									$er[$i] = OptLeaveType::whereIn('id', [1,2,4,5,7,9,10])->get()->sortBy('sorting');
								}
							}
						}
					}
				}
			}
		}
		// dd($i, $er);


		// https://select2.org/data-sources/formats
		// $cuti = [];
		foreach ($er as $key => $values) {
			$g = ['text' => $key, 'children' => []];
			foreach ($values as $value) {
 				$g['children'][] = [
										'id' => $value->id,
										'text' => $value->leave_type_code.' | '.$value->leave_type,
									];
			}
			$cuti['results'][] = $g;
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function unavailabledate(Request $request): JsonResponse
	{
		$blockdate = UnavailableDateTime::blockDate($request->id);

		$lusa1 = Carbon::now()->addDays(Setting::find(5)->active - 1)->format('Y-m-d');
		$period2 = \Carbon\CarbonPeriod::create(Carbon::now()->format('Y-m-d'), '1 days', $lusa1);
		$lusa = [];
		// dd(Setting::find(4)->active);
		if(Setting::find(4)->active == 1){															// enable N days checking : 1
			foreach ($period2 as $key1) {
				$lusa[] = $key1->format('Y-m-d');
			}
		}
		// dd($lusa);

		if($request->type == 1){
			$unavailableleave = Arr::collapse([$blockdate, $lusa]);
		}
		if($request->type == 2) {
			$unavailableleave = $blockdate;
		}
		return response()->json($unavailableleave);
	}

	public function unblockhalfdayleave(Request $request): JsonResponse
	{
		$blocktime = UnavailableDateTime::unblockhalfdayleave($request->id);
		return response()->json($blocktime);
	}

	public function backupperson(Request $request): JsonResponse
	{
		// we r going to find a backup person
		// 1st, we need to take a look into his/her department.
		// dd($request->all());
		$user = Staff::find($request->id);
		// dd($request->id, $user);
		$dept = $user->belongstomanydepartment()->wherePivot('main', 1)->first();
		$userindept = $dept->belongstomanystaff()->where('active', 1)->get();
		// dd($dept, $userindept);

		// backup from own department if he/she have
		// https://select2.org/data-sources/formats
		$backup['results'][] = [];
		if ($userindept->count()) {
			foreach($userindept as $key){
				if($key->id != $user->id){
					$chkavailability = $key->hasmanyleave()
									->where(function (Builder $query) use ($request){
										// $query->whereDate('date_time_start', '>=', '2023-09-21')
										// ->whereDate('date_time_start', '<=', '2023-09-22');
										$query->whereDate('date_time_start', '<=', $request->date_from)
										->whereDate('date_time_end', '>=', $request->date_to);
									})
									->where(function (Builder $query){
										$query->where('leave_type_id', '<>', 9)
										->where(function (Builder $query){
											$query->where('half_type_id', '<>', 2)
											->orWhereNull('half_type_id');
										});
									})
									->where(function (Builder $query){
										$query->whereIn('leave_status_id', [5,6])
											->orWhereNull('leave_status_id');
									})
									->get();
									// ->dumpRawSql();

					// dump($chkavailability);
					if($key->id != $chkavailability->first()?->staff_id) {
						$backup['results'][] = [
												'id' => $key->id,
												'text' => $key->name,
											];
					}
				}
			}
		}

		$crossbacku = $user->crossbackupto()?->wherePivot('active', 1)->get();
		// $crossbackup['results'][] = [];
		if($crossbacku) {
			foreach($crossbacku as $key){
				$chkavailability = $key->hasmanyleave()
								->where(function (Builder $query) use ($request){
									$query->whereDate('date_time_start', '<=', $request->date_from)
									->whereDate('date_time_end', '>=', $request->date_to);
								})
								->where(function (Builder $query){
									$query->where('leave_type_id', '<>', 9)
									->where(function (Builder $query){
										$query->where('half_type_id', '<>', 2)
										->orWhereNull('half_type_id');
									});
								})
								->where(function (Builder $query){
									$query->whereIn('leave_status_id', [5,6])
										->orWhereNull('leave_status_id');
								})
								->get();
								// ->dumpRawSql();

				if($key->id != $chkavailability->first()?->staff_id) {
					$backup['results'][] = [
											'id' => $key->id,
											'text' => $key->name,
										];
				}
			}
		}
		// $allbackups = Arr::collapse([$backup, $crossbackup]);
		// $allbackups = array_merge_recursive($backup, $crossbackup);
		// return response()->json( $allbackups );
		return response()->json( $backup );
	}

	public function timeleave(Request $request): JsonResponse
	{
		$whtime = UnavailableDateTime::workinghourtime($request->date, $request->id);
		return response()->json($whtime->first());
	}

	public function leavestatus(Request $request): JsonResponse
	{

		// $ls['results'] = [];
		if(\Auth::user()->belongstostaff->div_id != 2) {
			$c = OptLeaveStatus::where('id', '<>', 6)->where('id', '<>', 3)->get();
		} else {
			$c = OptLeaveStatus::where('id', '<>', 3)->get();
		}
		foreach ($c as $v) {
			$ls['results'][] = [
									'id' => $v->id,
									'text' => $v->status
								];
		}
		return response()->json($ls);
	}

	public function staffcrossbackup(Request $request): JsonResponse
	{
		$s = Staff::where('active', 1)->where('name','LIKE','%'.$request->search.'%')->get();
		foreach ($s as $v) {
				$ls['results'][] = [
										'id' => $v->id,
										'text' => Login::where([['active', 1], ['staff_id', $v->id]])->first()?->username.'  '.$v->name
									];
		}
		return response()->json($ls);
	}

	public function department(Request $request): JsonResponse
	{
		$au = DepartmentPivot::where([['category_id', $request->category_id], ['branch_id', $request->branch_id]])->get();
		foreach ($au as $key) {
			if($key->id != 31) {
				$cuti['results'][] = [
										'id' => $key->id,
										'text' => $key->department.' | '.$key->code,
									];
				// $cuti['pagination'] = ['more' => true];
				//for jquery-chained
				// $cuti[$key->id] = $key->department.' | '.$key->code;
			}
		}
		return response()->json( $cuti );
	}

	public function restdaygroup(Request $request): JsonResponse
	{
		$au = OptRestdayGroup::where('group','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->group,
								];
			// $cuti['pagination'] = ['more' => true];
			// $cuti[$key->id] = $key->department.' | '.$key->code;
		}
		return response()->json( $cuti );
	}

	public function authorise(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptAuthorise::where('group','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->authorise,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function branch(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptBranch::where('location','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->location,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function country(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptCountry::where('country','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->country,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function educationlevel(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptEducationLevel::where('education_level','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->education_level,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function gender(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptGender::where('gender','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->gender,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function status(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptStatus::where('status','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->status,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function category(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptCategory::where('category','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->category,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function healthstatus(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptHealthStatus::where('health_status','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->health_status,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function maritalstatus(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptMaritalStatus::where('marital_status','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->marital_status,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function race(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptRace::where('race','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->race,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function religion(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptReligion::where('religion','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->religion,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function taxexemptionpercentage(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptTaxExemptionPercentage::where('tax_exemption_percentage','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->tax_exemption_percentage,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function relationship(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptRelationship::where('relationship','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->relationship,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function division(Request $request): JsonResponse
	{
		// https://select2.org/data-sources/formats
		$au = OptDivision::where('div','LIKE','%'.$request->search.'%')->get();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->div,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function leaveevents(Request $request): JsonResponse
	{
		// please note that the full calendar for end date is EXCLUSIVE
		// https://fullcalendar.io/docs/event-object
		$l1 = HRLeave::
		where(function (Builder $query){
			$query->whereYear('date_time_start', date('Y'))->
			orWhereYear('date_time_start', Carbon::now()->addYear()->format('Y'));
		})
		->where(function (Builder $query){
			$query->whereIn('leave_status_id', [5,6])
			->orWhereNull('leave_status_id');
		})
		// ->ddRawSql();
		->get();
		// dump($l1);
		// $l2 = [];
		foreach ($l1 as $v) {
			$dts = \Carbon\Carbon::parse($v->date_time_start)->format('Y');
			$dte = \Carbon\Carbon::parse($v->date_time_end)->addDay()->format('j M Y g:i a');
			$arr = str_split( $dts, 2 );
			// only available if only now is before date_time_start and active is 1
			$dtsl = \Carbon\Carbon::parse( $v->date_time_start );
			$dt = \Carbon\Carbon::now()->lte( $dtsl );

			if (($v->leave_type_id == 9) || ($v->leave_type_id != 9 && $v->half_type_id == 2) || ($v->leave_type_id != 9 && $v->half_type_id == 1)) {
				$l2[] = [
							'title' => 'HR9-'.str_pad( $v->leave_no, 5, "0", STR_PAD_LEFT ).'/'.$arr[1],
							'start' => $v->date_time_start,
							'end' => $v->date_time_end,
							'url' => route('hrleave.show', $v->id),
							'allDay' => false,
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
							// 'description' => 'test',
					];

			} else {
				$l2[] = [
						'title' => 'HR9-'.str_pad( $v->leave_no, 5, "0", STR_PAD_LEFT ).'/'.$arr[1],
						'start' => $v->date_time_start,
						'end' => Carbon::parse($v->date_time_end)->addDay(),
						'url' => route('hrleave.show', $v->id),
						'allDay' => true,
						// 'extendedProps' => [
												// 'department' => 'BioChemistry'
											// ],
						// 'description' => 'test',
					];
			}
		}
			return response()->json( $l2 );
	}

	public function staffattendance(Request $request): JsonResponse
	{
		// this is for fullcalendar, its NOT INCLUSIVE for the last date
		// get the attandence 1st
		$attendance = HRAttendance::where('staff_id', $request->staff_id)->get();
		// foreach ($attendance as $s) {
		// 	if (Carbon::parse($s->attend_date) != Carbon::SUNDAY) {

		// 	}
		// }

		// mark sunday as a rest day
		$sun = Carbon::parse('2020-01-01')->toPeriod(Carbon::now()->addYear());
		foreach ($sun as $v) {
			if($v->dayOfWeek == 0){
				$l3[] = [
							'title' => 'RESTDAY',
							'start' => Carbon::parse($v)->format('Y-m-d'),
							'end' => Carbon::parse($v)->format('Y-m-d'),
							// 'url' => ,
							'allDay' => true,
							'description' => 'RESTDAY',
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
							'color' => 'grey',
							'textcolor' => 'white',
					];
			}
		}

		// mark saturday as restday
		$sat = Staff::find($request->staff_id)->belongstorestdaygroup?->hasmanyrestdaycalendar()->get();
		if (!is_null($sat)) {
			foreach ($sat as $v) {
				$l4[] = [
							'title' => 'RESTDAY',
							'start' => Carbon::parse($v->saturday_date)->format('Y-m-d'),
							'end' => Carbon::parse($v->saturday_date)->format('Y-m-d'),
							// 'url' => ,
							'allDay' => true,
							'description' => 'RESTDAY',
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
							'color' => 'grey',
							'textcolor' => 'white',
					];
			}
		} else {
			$l4[] = [];
		}

		// mark all holiday
		$hdate = HRHolidayCalendar::
				where(function (Builder $query){
					$query->whereYear('date_start', '<=', Carbon::now()->format('Y'))
					->orWhereYear('date_end', '>=', Carbon::now()->addYear(1)->format('Y'));
				})
				->get();
				// ->ddRawSql();
		if (!is_null($hdate)) {
			foreach ($hdate as $v) {
				$l1[] = [
							'title' => $v->holiday,
							'start' => $v->date_start,
							'end' => Carbon::parse($v->date_end)->addDay(),
							// 'url' => ,
							'allDay' => true,
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
							'description' => $v->holiday??'null',
							'color' => 'blue',
							'textColor' => 'white',
					];
			}
		} else {
			$l1[] = [];
		}

		// looking for leave of each staff
		$l = HRLeave::where('staff_id', $request->staff_id)
					->where(function (Builder $query) {
						$query->whereIn('leave_status_id', [5,6])->orWhereNull('leave_status_id');
					})
					->get();

		// if(!is_null($l)) {
		if($l->count()) {
			foreach ($l as $v) {
				$dts = Carbon::parse($v->date_time_start)->format('Y');
				$dte = Carbon::parse($v->date_time_end)->addDay()->format('j M Y g:i a');
				// only available if only now is before date_time_start and active is 1
				$dtsl = Carbon::parse( $v->date_time_start );
				$dt = Carbon::now()->lte( $dtsl );

				if (($v->leave_type_id == 9) || ($v->leave_type_id != 9 && $v->half_type_id == 2) || ($v->leave_type_id != 9 && $v->half_type_id == 1)) {
					$l2[] = [
								'title' => 'HR9-'.str_pad( $v->leave_no, 5, "0", STR_PAD_LEFT ).'/'.$v->leave_year,
								'start' => $v->date_time_start,
								'end' => $v->date_time_end,
								'url' => route('hrleave.show', $v->id),
								'allDay' => false,
								// 'extendedProps' => [
								// 						'department' => 'BioChemistry'
								// 					],
								'description' => $v->belongstooptleavetype?->leave_type_code??'null',
								'color' => 'purple',
								'textColor' => 'white',
								'borderColor' => 'purple',
						];

				} else {
					$l2[] = [
							'title' => 'HR9-'.str_pad( $v->leave_no, 5, "0", STR_PAD_LEFT ).'/'.$v->leave_year,
							'start' => $v->date_time_start,
							'end' => Carbon::parse($v->date_time_end)->addDay(),
							'url' => route('hrleave.show', $v->id),
							'allDay' => true,
							// 'extendedProps' => [
													// 'department' => 'BioChemistry'
												// ],
							'description' => $v->belongstooptleavetype?->leave_type_code??'null',
							'color' => 'purple',
							'textColor' => 'white',
							'borderColor' => 'red',
						];
				}
			}
		} else {
			$l2[] = [];
		}

		$outstation = HROutstation::where('staff_id', $request->staff_id)->where('active', 1)->get();
		if ($outstation->isNotEmpty()) {
			foreach ($outstation as $v) {
				$l5[] = [
							'title' => 'Outstation',
							'start' => $v->date_from,
							'end' => Carbon::parse($v->date_to)->addDay(),
							// 'url' => route('hrleave.show', $v->id),
							'allDay' => true,
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
							'description' => $v->belongstocustomer?->customer??$v->remarks??'null',
							'color' => 'teal',
							'textColor' => 'yellow',
							'borderColor' => 'green',
					];
			}
		} else {
			$l5[] = [];
		}
		$l0 = array_merge($l1, $l2, $l3, $l4, $l5);
		return response()->json( $l0 );
	}

	public function staffattendancelist(Request $request): JsonResponse
	{
		$sa = HRAttendance::leftjoin('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
			->select('hr_attendances.staff_id', 'logins.username')
			->where(function (Builder $query) use ($request){
				$query->whereDate('hr_attendances.attend_date', '>=', $request->from)
				->whereDate('hr_attendances.attend_date', '<=', $request->to);
			})
			->where('logins.active', 1)
			->groupBy('hr_attendances.staff_id')
			->orderBy('logins.username', 'ASC')
			->get();
		foreach ($sa as $v) {
			$l0[] = ['id' => $v->staff_id, 'username' => $v->username, 'name' => Staff::find($v->staff_id)->name, 'branch' => Staff::find($v->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id, 'department' => Staff::find($v->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->department];
		}
		return response()->json( $l0 );
	}

	public function branchattendancelist(Request $request): JsonResponse
	{
		$sa = OptBranch::all();
		foreach ($sa as $v) {
			$l1[] = ['id' => $v->id, 'location' => $v->location];
		}
		return response()->json( $l1 );
	}

	public function staffpercentage(Request $request): JsonResponse
	{
		$st = Staff::find($request->id);					// need to check date join

		$soy = now()->copy()->startOfYear();				// early this year
		$lsoy = $soy->copy()->subYear();					// early last year
		// dd($lsoy);
		// dd($lsoy->diffInMonths(now()));

		for ($i = 0; $i <= $soy->diffInMonths(now()); $i++) {// take only 2 years back
			$sm = $soy->copy()->addMonth($i);
			$em = $sm->copy()->endOfMonth();
			// dump([$sm, $em]);

			$sq = $st->hasmanyattendance()
				->whereDate('attend_date', '>=', $sm)
				->whereDate('attend_date', '<=', $em)
				->where('daytype_id', 1)
				->get();
				// ->ddRawSql();

				$fdl = 0;
				$a = 0;
			if ($sq->count()) {
				$workday = $sq->count();														// working days
				// dump([$workday, $sm->format('M Y')]);

				foreach ($sq as $s) {
					$fulldayleave = $s->belongstoleave()?->where(function (Builder $query){
					// $fulldayleave = HRLeave::where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where(function (Builder $query){
												$query->where('half_type_id', '<>', 2)
												->orWhereNull('half_type_id');
											});
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
											->orWhereNull('leave_status_id');
										})
										->where(function (Builder $query) use ($s){
											$query->whereDate('date_time_start', '<=', $s->attend_date)
											->WhereDate('date_time_end', '>=', $s->attend_date);
										})
										->get();
					$fdl += $fulldayleave->count();
					// dump($fulldayleave->count().' fulldayleave count');

					$absent = $s->where('attendance_type_id', 1)
					// $absent = HRAttendance::where('attendance_type_id', 1)
								->whereDate('attend_date', $s->attend_date)
								->where('daytype_id', 1)
								->where('staff_id', $st->id)
								->get();
					$a += $absent->count();
					// dump($absent.' absent');
				}
				$percentage = (($workday - $fdl - $a) / $workday) * 100;
			} else {
				$workday = 0;
				// $fdl = 0;
				$percentage = 0;
			}

			$chartdata[] = [
								'month' => $sm->format('M Y'),
								'percentage' => $percentage,
								'workdays' => $workday,
								'leaves' => $fdl,
								'absents' => $a,
								'working_days' => ($workday - $fdl - $a),
							];
		}
		return response()->json($chartdata);
	}

	public function yearworkinghourstart(Request $request): JsonResponse
	{
		$valid = TRUE;

		$po = OptWorkingHour::groupBy('year')->select('year')->get();

		foreach ($po as $k1) {
			if($k1->year == \Carbon\Carbon::parse($request->effective_date_start)->format('Y')) {
				$valid = FALSE;
			}
		}

		return response()->json([
			'year1' => \Carbon\Carbon::parse($request->effective_date_start)->format('Y'),
			'valid' => $valid
		]);
	}

	public function yearworkinghourend(Request $request): JsonResponse
	{
		$valid = TRUE;

		$po = OptWorkingHour::groupBy('year')->select('year')->get();

		foreach ($po as $k2) {
			if($k2->year == \Carbon\Carbon::parse($request->effective_date_end)->format('Y')) {
				$valid = FALSE;
			}
		}

		return response()->json([
			'year2' => \Carbon\Carbon::parse($request->effective_date_end)->format('Y'),
			'valid' => $valid
		]);
	}

	public function hcaldstart(Request $request): JsonResponse
	{
		$valid = true;
		// echo $request->date_start;
		$u = HRHolidayCalendar::all();
		foreach($u as $p) {
			$b = \Carbon\CarbonPeriod::create($p->date_start, '1 day', $p->date_end);
			// echo $p->date_start;
			// echo $p->date_end;
			foreach ($b as $key) {
				// echo $key;
				if($key->format('Y-m-d') == $request->date_start) {
					$valid = false;
				}
			}
		}
		return response()->json([
			'valid' => $valid,
		]);
	}

	public function hcaldend(Request $request): JsonResponse
	{
		$valid = true;
		// echo $request->date_end;
		$u = HRHolidayCalendar::all();
		foreach($u as $p) {
			$b = \Carbon\CarbonPeriod::create($p->date_start, '1 day', $p->date_end);
			// echo $p->date_start;
			// echo $p->date_end;
			foreach ($b as $key) {
				// echo $key;
				if($key->format('Y-m-d') == $request->date_end) {
					$valid = false;
				}
			}
		}
		return response()->json([
			'valid' => $valid,
		]);
	}

	public function staffdaily(Request $request): JsonResponse
	{
		$now = now();
		$lsoy = $now->copy()->subDays(6);								// 6 days ago

		$b = 0;
		for ($i = 0; $i <= $lsoy->copy()->diffInDays($now->copy()); $i++) {
			$sd = $lsoy->copy()->addDays($i);
			// dump($sd);

			$sq = HRAttendance::whereDate('attend_date', $sd)->groupBy('attend_date')->get();
			// dump($sq);
			// exit;
			$workday1 = HRAttendance::whereDate('attend_date', $sd)->where('daytype_id', 1)->get();
			$workday = $workday1->count();

			// dump($sq->first()->daytype_id);
			// dump($workday);
			if ($workday >= 1) {
				if (Carbon::parse($sd)->dayOfWeek == Carbon::SATURDAY) {
					$working = OptDayType::find(1)->daytype;
				} else {
					$working = OptDayType::find($sq->first()->daytype_id)->daytype;
				}
				$workingpeople1 = HRAttendance::whereDate('attend_date', $sd)->where('daytype_id', 1)->whereNull('outstation_id')->whereNull('leave_id')->get();
				$workingpeople = $workingpeople1->count();
				$outstation1 = HRAttendance::whereDate('attend_date', $sd)->where('daytype_id', 1)->whereNotNull('outstation_id')->get();
				$outstation = $outstation1->count();
				$absent1 = HRAttendance::whereDate('attend_date', $sd)->where('daytype_id', 1)->where('attendance_type_id', 1)->get();
				$absent = $absent1->count();
				$halfabsent1 = HRAttendance::whereDate('attend_date', $sd)->where('daytype_id', 1)->where('attendance_type_id', 2)->get();
				$halfabsent = $halfabsent1->count();
				// $leave1 = HRLeave::where(function (Builder $query){
				// 						$query->where('leave_type_id', '<>', 9)
				// 						->where(function (Builder $query){
				// 							$query->where('half_type_id', '<>', 2)
				// 							->orWhereNull('half_type_id');
				// 						});
				// 					})
				// 					->where(function (Builder $query){
				// 						$query->whereIn('leave_status_id', [5,6])
				// 						->orWhereNull('leave_status_id');
				// 					})
				// 					->where(function (Builder $query) use ($sd){
				// 						$query->whereDate('date_time_start', '<=', $sd)
				// 						->whereDate('date_time_end', '>=', $sd);
				// 					});																							// this will get only full day leave
				$leave1 = HRAttendance::whereDate('attend_date', $sd)->where('daytype_id', 1)->whereNotNull('leave_id');		// this will get all leave including TF and half day leave
				// $leave = $leave1->ddrawsql();
				$leave = $leave1->count();

				$e = 0;
				if ($absent) {
					foreach ($absent1 as $staffidabsent) {
						$branch[$b][$e] = Staff::find($staffidabsent->staff_id)
									->belongstomanydepartment()?->wherePivot('main', 1)
									->first()->belongstobranch?->location;
						$e++;
					}
				} else {
					$branch[$b] = [];
				}
				if (array_key_exists($b, $branch)) {
					$locabsent1 = array_count_values($branch[$b]);
				} else {
					$locabsent1 = json_decode("{}");
				}

				$eh = 100;
				if($halfabsent) {
					foreach ($halfabsent1 as $staffidhalfabsent) {
						$branchhalfabsent[$b][$eh] = Staff::find($staffidhalfabsent->staff_id)
									->belongstomanydepartment()?->wherePivot('main', 1)
									->first()->belongstobranch?->location;
						$eh++;
					}
				} else {
					$branchhalfabsent[$b] = [];
				}
				if (array_key_exists($b, $branchhalfabsent)) {
					$lochalfabsent1 = array_count_values($branchhalfabsent[$b]);
				} else {
					$lochalfabsent1 = json_decode("{}");
				}

				$eo = 200;
				if ($outstation) {
					foreach ($outstation1 as $staffidoutstation) {
						$branchoutstaion[$b][$eo] = Staff::find($staffidoutstation->staff_id)
									->belongstomanydepartment()?->wherePivot('main', 1)
									->first()?->belongstobranch?->location;
						$eo++;
					}
				} else {
					$branchoutstaion[$b] = [];
				}
				if (array_key_exists($b, $branchoutstaion)) {
					$locoutstation1 = array_count_values($branchoutstaion[$b]);
				} else {
					$locoutstation1 = json_decode("{}");
				}

				$leave1 = $leave1->get();
				$ep = 300;
				if ($leave) {
					foreach ($leave1 as $staffidleaveloc) {
						$branchleave[$b][$ep] = Staff::find($staffidleaveloc->staff_id)
									->belongstomanydepartment()?->wherePivot('main', 1)
									->first()->belongstobranch?->location;
						$ep++;
					}
					// exit;
				} else {
					$branchleave[$b] = [];
				}
				if (array_key_exists($b, $branchleave)) {
					$locleave1 = array_count_values($branchleave[$b]);
				} else {
					$locleave1 = json_decode("{}");
				}
				$overallpercentage = number_format(((($workingpeople + $outstation) - $absent - $leave) / ($workingpeople + $outstation)) * 100, 2);

			} else {

				$workingpeople1 = HRAttendance::whereDate('attend_date', $sd)
												->where(function(Builder $query) {
													$query->where('in', '!=', '00:00:00')
														->orwhere('break', '!=', '00:00:00')
														->orwhere('resume', '!=', '00:00:00')
														->orwhere('out', '!=', '00:00:00');
												})
												->whereNull('outstation_id')
												->whereNull('leave_id')
												->get();
				$workingpeople = $workingpeople1->count();
				$outstation1 = HRAttendance::whereDate('attend_date', $sd)->whereNotNull('outstation_id')->get();
				$outstation = $outstation1->count();
				$absent = 0;
				$halfabsent = 0;
				$leave = 0;
				$working = OptDayType::find($sq->first()?->daytype_id)?->daytype;
				// $locabsent1 = [];
				// $lochalfabsent1 = [];
				// $locoutstation1 = [];
				// $locleave1 = [];
				$locabsent1 = json_decode("{}");
				$lochalfabsent1 = json_decode("{}");
				$locoutstation1 = json_decode("{}");
				$locleave1 = json_decode("{}");
				// dump($workingpeople);
				$available = $workingpeople + $outstation;

				if ($available == 0) {
					$availableppl = 1;
					$workday = 0;
				} else {
					$availableppl = $available;
					$workday = $available;
				}


				$overallpercentage = number_format((($available - $absent - $leave) / ($availableppl)) * 100, 2);
				// $overallpercentage = 0;
			}

			$chartdata[$b] = [
								'date' => Carbon::parse($sd)->format('j M Y'),
								'overallpercentage' => $overallpercentage,
								'workday' => $workday,
								'workingpeople' => $workingpeople,
								'working' => $working,
								'outstation' => $outstation,
								'leave' => $leave,
								'absent' => $absent,
								'halfabsent' => $halfabsent,
								'locoutstation' => $locoutstation1,
								'locationleave' => $locleave1,
								'locationabsent' => $locabsent1,
								'locationhalfabsent' => $lochalfabsent1,
							];
			$b++;
		}
		return response()->json($chartdata);
	}

	public function samelocationstaff(Request $request): JsonResponse
	{
		$me = Staff::find($request->id);
		$mede = $me->belongstomanydepartment()->wherePivot('main', 1)->first();
		$branch = $mede->branch_id;
		if ($me->div_id == 1 || $me->div_id == 2 || $me->div_id == 5) {
			$dep = DepartmentPivot::where([['category_id', 2]])->get();
		} elseif ($me->div_id == 4) {
			$dep = DepartmentPivot::where([['branch_id', $branch], ['category_id', 2]])->get();
		} elseif ($me->authorise_id == 1) {
			$dep = DepartmentPivot::all();
		} elseif (is_null($me->div_id) || is_null($me->authorise_id)) {
			$dep = DepartmentPivot::find(0);
		}

		// dd($dep);
		foreach ($dep as $v) {
			$staff = $v->belongstomanystaff()->wherePivot('main', 1)->where('active', 1)->where('name','LIKE','%'.$request->search.'%')->get();
			foreach ($staff as $k) {
				$s['results'][] = ['id' => $k->id, 'text' => $k->name];
			}
		}
		return response()->json($s);
	}

	public function overtimerange(): JsonResponse
	{
		$or = HROvertimeRange::where('active', 1)->get();
		foreach ($or as $v) {
		    $l['results'][] = ['id' => $v->id, 'text' => $v->start.' => '.$v->end];
		}
		return response()->json($l);
	}

	public function outstationattendancelocation(Request $request): JsonResponse
	{
		$st = HROutstation::where(function (Builder $query) use ($request) {
								$query->whereDate('date_from', '<=', $request->date_attend)
								->whereDate('date_to', '>=', $request->date_attend);
							})
							->where('active', 1)
							->groupBy('customer_id')
							->get();
							// ->ddrawsql();

		// https://select2.org/data-sources/formats
		foreach ($st as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => Customer::find($key->customer_id)?->customer,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json($cuti);
	}

	public function outstationattendancestaff(Request $request): JsonResponse
	{
		$st = HROutstation::
							// where(function (Builder $query) use ($request) {
							// 	$query->whereDate('date_from', '<=', $request->date_attend)
							// 	->whereDate('date_to', '>=', $request->date_attend);
							// })
							where('id', $request->outstation_id)
							->where('active', 1)
							// ->groupBy('staff_id')
							->first();
							// ->ddrawsql();
		$cust = HROutstation::where(function (Builder $query) use ($request) {
									$query->whereDate('date_from', '<=', $request->date_attend)
									->whereDate('date_to', '>=', $request->date_attend);
								})
								->where('customer_id', $st->customer_id)
								->where('active', 1)
								// ->ddrawsql();
								->get() ;

		// https://select2.org/data-sources/formats
		foreach ($cust as $key) {
			$cuti['results'][] = [
									'id' => $key->staff_id,
									'text' => Staff::find($key->staff_id)->name,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json($cuti);
	}

	public function staffoutstationduration(Request $request): JsonResponse
	{
		$outstation = HROutstation::where('active', 1)->get();
		if ($outstation->count()) {
			foreach ($outstation as $v) {
				$out[] = [
							'title' => ucwords(Str::lower($v->belongstocustomer?->customer??$v->remarks)),
							'start' => $v->date_from,
							'end' => Carbon::parse($v->date_to)->addDay(),
							// 'url' => route('hrleave.show', $v->id),
							'allDay' => true,
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
							'description' => ((Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username)??'-').' '.Staff::find($v->staff_id)->name,
							'color' => 'green',
							'textColor' => 'yellow',
							'borderColor' => 'green',
					];
			}
		} else {
			$out[] = [];
		}
		return response()->json( $out );
	}




































	// used by queue batches
	public function progress(Request $request): JsonResponse
	{
		if (!$request->id) {
			if (session()->exists('lastBatchId')) {
				$bid = session()->get('lastBatchId');
			} elseif (session()->exists('lastBatchIdPay')) {
				$bid = session()->get('lastBatchIdPay');
			} else {
				$bid = 1;
			}
		} else {
			$bid = $request->id;
		}
		$batch = Bus::findBatch($bid);
		return response()->json([
			'processedJobs' => $batch->processedJobs(),
			'totalJobs' => $batch->totalJobs,
			'progress' => $batch->progress()
		]);
	}
}

