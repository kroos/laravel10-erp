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
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveEntitlement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRAttendance;

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
use App\Models\HumanResources\OptStatus;
use App\Models\HumanResources\DepartmentPivot;
use App\Models\HumanResources\HROvertime;

use Illuminate\Database\Eloquent\Builder;

// load helper
use App\Helpers\UnavailableDateTime;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

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
		// tahun sekarang ni
		$year = \Carbon\Carbon::parse(now())->year;

		$user = \App\Models\Staff::find($request->id);
		// checking for annual leave, mc, nrl and maternity
		// hati-hati dgn yg ni sbb melibatkan masa
		$leaveAL =  $user->hasmanyleaveannual()->where('year', date('Y'))->first();
		$leaveMC =  $user->hasmanyleavemc()->where('year', date('Y'))->first();
		$leaveMa =  $user->hasmanyleavematernity()->where('year', date('Y'))->first();
		// cari kalau ada replacement leave
		$oi = $user->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->whereYear('date_start', date('Y'))->get();

		// dd($oi->sum('leave_balance'));

		if(Setting::where('id', 3)->first()->active == 1){																		// special unpaid leave activated
			if($user->gender_id == 1){																							// laki
				if($oi->sum('leave_balance') < 0.5){																			// laki | no nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// laki | no nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | no nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,6,9,11,12])->get()->sortBy('sorting');
						} else {																								// laki | no nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,6,9,12])->get()->sortBy('sorting');
						}
					} else {																									// laki | no nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | no nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,5,9,11,12])->get()->sortBy('sorting');
						} else {																								// laki | no nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,5,9,12])->get()->sortBy('sorting');
						}
					}
				} else {																										// laki | nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// laki | nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,4,6,9,10,11,12])->get()->sortBy('sorting');
						} else {																								// laki | nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,4,6,9,10,12])->get()->sortBy('sorting');
						}
					} else {																									// laki | nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,4,5,9,10,11,12])->get()->sortBy('sorting');
						} else {																								// laki | nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,4,5,9,10,12])->get()->sortBy('sorting');
						}
					}
				}
			} else {																											// pempuan
				if($oi->sum('leave_balance') < 0.5){																			// pempuan | no nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// pempuan | no nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | no nrl | no al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | no al |  no mc | no maternity
								$er = OptLeaveType::whereIn('id', [3,6,9,11,12])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | no al |  no mc | maternity
								$er = OptLeaveType::whereIn('id', [3,6,7,9,11,12])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | no nrl | no al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | no al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [2,3,6,9,12])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | no al | mc | maternity
								$er = OptLeaveType::whereIn('id', [2,3,6,7,9,12])->get()->sortBy('sorting');
							}
						}
					} else {																									// pempuan | no nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | no nrl | al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,5,9,11,12])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [1,5,7,9,11,12])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | no nrl | al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,2,5,9,12])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | al | mc | maternity
								$er = OptLeaveType::whereIn('id', [1,2,5,7,9,12])->get()->sortBy('sorting');
							}
						}
					}
				} else {																										// pempuan | nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// pempuan | nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | nrl | no al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [3,4,6,7,9,10,11,12])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | no al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [3,4,6,7,9,10,11,12])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | nrl | no al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [2,3,4,6,9,10,12])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | no al | mc | maternity
								$er = OptLeaveType::whereIn('id', [2,3,4,6,7,9,10,12])->get()->sortBy('sorting');
							}
						}
					} else {																									// pempuan | nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | nrl | al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,4,5,9,10,11,12])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [1,4,5,7,9,10,11,12])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | nrl | al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,2,4,5,9,10,12])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | al | mc | maternity
								$er = OptLeaveType::whereIn('id', [1,2,4,5,7,9,10,12])->get()->sortBy('sorting');
							}
						}
					}
				}
			}
		} else {																												// special unpaid leave deactivated
			if($user->gender_id == 1){																							// laki
				if($oi->sum('leave_balance') < 0.5){																			// laki | no nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// laki | no nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | no nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,6,9,11])->get()->sortBy('sorting');
						} else {																								// laki | no nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,6,9])->get()->sortBy('sorting');
						}
					} else {																									// laki | no nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | no nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,5,9,11])->get()->sortBy('sorting');
						} else {																								// laki | no nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,5,9])->get()->sortBy('sorting');
						}
					}
				} else {																										// laki | nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// laki | nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,4,6,9,10,11])->get()->sortBy('sorting');
						} else {																								// laki | nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,4,6,9,10])->get()->sortBy('sorting');
						}
					} else {																									// laki | nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// laki | nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,4,5,9,10,11])->get()->sortBy('sorting');
						} else {																								// laki | nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,4,5,9,10])->get()->sortBy('sorting');
						}
					}
				}
			} else {																											// pempuan
				if($oi->sum('leave_balance') < 0.5){																			// pempuan | no nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// pempuan | no nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | no nrl | no al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [3,6,9,11])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | al | mc | maternity
								$er = OptLeaveType::whereIn('id', [3,6,7,9,11])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | no nrl | no al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | no al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [2,3,6,9])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | no al | mc | maternity
								$er = OptLeaveType::whereIn('id', [2,3,6,7,9])->get()->sortBy('sorting');
							}
						}
					} else {																									// pempuan | no nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | no nrl | al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,5,7,9,11])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [1,5,7,9,11])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | no nrl | al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | no nrl | al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,2,5,9])->get()->sortBy('sorting');
							} else {																							// pempuan | no nrl | al | mc | maternity
								$er = OptLeaveType::whereIn('id', [1,2,5,7,9])->get()->sortBy('sorting');
							}
						}
					}
				} else {																										// pempuan | nrl
					if($leaveAL->annual_leave_balance < 0.5){																	// pempuan | nrl | no al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | nrl | no al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [3,4,6,9,10,11])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | no al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [3,4,6,7,9,10,11])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | nrl | no al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | no al | mc | no maternity
								$er = OptLeaveType::whereIn('id', [2,3,4,6,9,10])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | no al | mc | maternity
								$er = OptLeaveType::whereIn('id', [2,3,4,6,7,9,10])->get()->sortBy('sorting');
							}
						}
					} else {																									// pempuan | nrl | al
						if($leaveMC->mc_leave_balance < 0.5){																	// pempuan | nrl | al | no mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,4,5,9,10,11])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [1,4,5,7,9,10,11])->get()->sortBy('sorting');
							}
						} else {																								// pempuan | nrl | al | mc
							if($leaveMa->maternity_leave_balance < 0.5){														// pempuan | nrl | al | no mc | no maternity
								$er = OptLeaveType::whereIn('id', [1,2,4,5,9,10])->get()->sortBy('sorting');
							} else {																							// pempuan | nrl | al | no mc | maternity
								$er = OptLeaveType::whereIn('id', [1,2,4,5,7,9,10])->get()->sortBy('sorting');
							}
						}
					}
				}
			}
		}

		// https://select2.org/data-sources/formats
		foreach ($er as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->leave_type_code.' | '.$key->leave_type,
								];
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
		$user = Staff::find($request->id);
		// dd($user);
		$dept = $user->belongstomanydepartment()->first();
		$userindept = $dept->belongstomanystaff()->where('active', 1)->get();

		// backup from own department if he/she have
		// https://select2.org/data-sources/formats
		$backup['results'][] = [];
		if ($userindept) {
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
										'text' => $v->name
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
							// 'description' => '',
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
		if ($sat->isNotEmpty()) {
			foreach ($sat as $v) {
				$l4[] = [
							'title' => 'RESTDAY',
							'start' => Carbon::parse($v->saturday_date)->format('Y-m-d'),
							'end' => Carbon::parse($v->saturday_date)->format('Y-m-d'),
							// 'url' => ,
							'allDay' => true,
							// 'description' => '',
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
		if ($hdate->isNotEmpty()) {
			foreach ($hdate as $v) {
				$l1[] = [
							'title' => $v->holiday,
							'start' => $v->date_start,
							'end' => Carbon::parse($v->date_end)->addDay(),
							// 'url' => ,
							'allDay' => true,
							'description' => $v->holiday,
							// 'extendedProps' => [
							// 						'department' => 'BioChemistry'
							// 					],
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

		if($l->isNotEmpty()) {
			foreach ($l as $v) {
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
								'color' => 'purple',
								'textColor' => 'white',
								'borderColor' => 'purple',
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
							// 'description' => 'test',
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
		$sa = HRAttendance::select('staff_id')
			->where(function (Builder $query) use ($request){
				$query->whereDate('attend_date', '>=', $request->from)
				->whereDate('attend_date', '<=', $request->to);
			})
			->groupBy('hr_attendances.staff_id')
			->get();
		foreach ($sa as $v) {
			$l0[] = ['id' => $v->staff_id, 'name' => Staff::where('id', $v->staff_id)->first()->name];
		}
		return response()->json( $l0 );
	}

	public function staffpercentage1(Request $request): JsonResponse
	{
		// dd($request->all());
		// $request->id = staff id
		Carbon::setWeekendDays([
			// Carbon::SATURDAY,
			Carbon::SUNDAY,
		]);

		$st = Staff::find($request->id);													// need to check date join
		$join = Carbon::parse($st->join);													// join date
		// dump($join);																		//2023-03-09

		$now = Carbon::now();																// todays date

		$checkmonthsago = $join->copy()->diffInMonths($now);								// how many months ago?
		// dump($checkmonthsago);																//6

		$nowstartmonth = $now->copy()->startOfMonth();										// 1st day of month
		// dump($nowstartmonth);															//

		$sixstart = $nowstartmonth->copy()->subMonths(6);											// getting start day of 6 months before
		$sixend = $sixstart->copy()->endOfMonth();											// getting end day of 6 months before
		$sixmonthname = $sixstart->copy()->monthName;										// getting name of the 6 months before

		$fivestart = $sixstart->copy()->addMonth();											// getting start day of 5 months before
 		$fiveend = $fivestart->copy()->endOfMonth();										// getting end day of 5 months before
 		$fivemonthname = $fivestart->copy()->monthName;									// getting name of the 6 months before

		$fourstart = $fivestart->copy()->addMonth();										// getting start day of 4 months before
 		$fourend = $fourstart->copy()->endOfMonth();										// getting end day of 4 months before
 		$fourmonthname = $fourstart->copy()->monthName;									// getting name of the 6 months before

		$threestart = $fourstart->copy()->addMonth();										// getting start day of 3 months before
 		$threeend = $threestart->copy()->endOfMonth();										// getting end day of 3 months before
 		$threemonthname = $threestart->copy()->monthName;									// getting name of the 6 months before

		$twostart = $threestart->copy()->addMonth();										// getting start day of 2 months before
 		$twoend = $twostart->copy()->endOfMonth();											// getting end day of 2 months before
 		$twomonthname = $twostart->copy()->monthName;										// getting name of the 6 months before

		$onestart = $twostart->copy()->addMonth();											// getting start day of 1 months before
 		$oneend = $onestart->copy()->endOfMonth();											// getting end day of 1 months before
 		$onemonthname = $onestart->copy()->monthName;										// getting name of the 6 months before

		$start = $onestart->copy()->addMonth();											// getting start day of 1 months before
 		$end = $start->copy()->endOfMonth();											// getting end day of 1 months before
 		$monthname = $start->copy()->monthName;										// getting name of the 6 months before

		// dump([$sixstart, $sixend, $fivestart, $fiveend, $fourstart, $fourend, $threestart, $threeend, $twostart, $twoend, $onestart, $oneend]);
		// dump($checkmonthsago >= 6);														//true
		// dump($join->gte($sixstart));														//true
		// dump($join->gte($sixstart));														//true


		if ($checkmonthsago >= 6) {															// meaning he join 6 months ago
			if ($join->gte($sixstart)) {													// check if he join in the same month, count from $join
				$sixm = $join->toPeriod($sixend);											// 23 days
				// dump($sixm->count());													// 23 days
				// dump($sixend);
				$nosixweekend = $join->diffInWeekdays($sixend);								// get weekdays from above as we have only sunday as a weekend
				// dump($nosixweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$sixholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $sixend){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $sixend);
													})
													->get();
													// ->ddRawSql();
				// dump($sixholiday);
				$q = 0;
				if ($sixholiday) {
					foreach ($sixholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}
				// dump($q);

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $sixend){
									$query->whereDate('saturday_date', '<=', $sixend)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $sixend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $sixend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $sixend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $sixend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $sixend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $sixend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $sixend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($sixend, $join){
										$query->whereDate('date_time_start', '<=', $sixend)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($sixend, $join){
												$query->whereDate('date_time_start', '<=', $sixend)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $sixmonthname;
				$workday = ($nosixweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			} else {
				$sixm = $sixstart->toPeriod($sixend);										// 23 days
				// dump($sixm->count());														// 23 days
				// dump($sixend);
				$nosixweekend = $sixstart->diffInWeekdays($sixend, true);					// get weekdays from above as we have only sunday as a weekend
				// dump($nosixweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$sixholiday = HRHolidayCalendar::where(function (Builder $query) use ($sixstart, $sixend){
														$query->whereDate('date_start', '>=', $sixstart)
														->WhereDate('date_start', '<=', $sixend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($sixholiday) {
					foreach ($sixholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							} else {
								$q++;
							}
						}
					}
				}

				// getting holiday on that month
				$sixholiday = HRHolidayCalendar::where(function (Builder $query) use ($sixstart, $sixend){
														$query->whereDate('date_start', '>=', $sixstart)
														->WhereDate('date_start', '<=', $sixend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($sixholiday) {
					foreach ($sixholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}
				// dump($sixperiod->count());

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($sixstart, $sixend){
									$query->whereDate('saturday_date', '<=', $sixend)
									->WhereDate('saturday_date', '>=', $sixstart);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($sixstart, $sixend){
											$query->whereDate('attend_date', '>=', $sixstart)
											->whereDate('attend_date', '<=', $sixend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $sixstart to $sixend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($sixstart, $sixend){
											$query->whereDate('attend_date', '>=', $sixstart)
											->whereDate('attend_date', '<=', $sixend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($sixstart, $sixend){
											$query->whereDate('attend_date', '>=', $sixstart)
											->whereDate('attend_date', '<=', $sixend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($sixend, $sixstart){
										$query->whereDate('date_time_start', '<=', $sixend)
										->WhereDate('date_time_end', '>=', $sixstart);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($sixend, $sixstart){
												$query->whereDate('date_time_start', '<=', $sixend)
												->WhereDate('date_time_end', '>=', $sixstart);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $sixmonthname;
				$workday = ($nosixweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $month,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}

		if($checkmonthsago >= 5) {
			if ($join->gte($fivestart)) {													// check if he join in the same month, count from $join
				$fivem = $join->toPeriod($fiveend);											// 23 days
				// dump($fivem->count());													// 23 days
				// dump($fiveend);
				$nofiveweekend = $join->diffInWeekdays($fiveend);								// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$fiveholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $fiveend){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $fiveend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($fiveholiday) {
					foreach ($fiveholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $fiveend){
									$query->whereDate('saturday_date', '<=', $fiveend)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $fiveend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $fiveend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $fiveend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $fiveend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $fiveend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $fiveend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $fiveend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($fiveend, $join){
										$query->whereDate('date_time_start', '<=', $fiveend)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)								// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($fiveend, $join){
												$query->whereDate('date_time_start', '<=', $fiveend)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $fivemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);

			} else {																			// count from $fiveend
				$fivem = $fivestart->toPeriod($fiveend);										// 23 days
				// dump($fivem->count());														// 23 days
				// dump($fiveend);
				$nofiveweekend = $fivestart->diffInWeekdays($fiveend, true);					// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$fiveholiday = HRHolidayCalendar::where(function (Builder $query) use ($fivestart, $fiveend){
														$query->whereDate('date_start', '>=', $fivestart)
														->WhereDate('date_start', '<=', $fiveend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($fiveholiday) {
					foreach ($fiveholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($fivestart, $fiveend){
									$query->whereDate('saturday_date', '<=', $fiveend)
									->WhereDate('saturday_date', '>=', $fivestart);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($fivestart, $fiveend){
											$query->whereDate('attend_date', '>=', $fivestart)
											->whereDate('attend_date', '<=', $fiveend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $fivestart to $fiveend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($fivestart, $fiveend){
											$query->whereDate('attend_date', '>=', $fivestart)
											->whereDate('attend_date', '<=', $fiveend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($fivestart, $fiveend){
											$query->whereDate('attend_date', '>=', $fivestart)
											->whereDate('attend_date', '<=', $fiveend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($fiveend, $fivestart){
										$query->whereDate('date_time_start', '<=', $fiveend)
										->WhereDate('date_time_end', '>=', $fivestart);
								})
								->get();
								// ->ddRawSql();
				// dd($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dd($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($fiveend, $fivestart){
												$query->whereDate('date_time_start', '<=', $fiveend)
												->WhereDate('date_time_end', '>=', $fivestart);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $fivemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $month,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}

		if ($checkmonthsago >= 4) {
			if ($join->gte($fourstart)) {													// check if he join in the same month, count from $join
				$fourm = $join->toPeriod($fourend);											// 23 days
				// dump($fourm->count());													// 23 days
				// dump($fourend);
				$nofiveweekend = $join->diffInWeekdays($fourend);								// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$fourholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $fourend){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $fourend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($fourholiday) {
					foreach ($fourholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $fourend){
									$query->whereDate('saturday_date', '<=', $fourend)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $fourend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $fourend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $fourend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $fourend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $fourend);
										})
										// ->get();
										->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $fourend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $fourend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($fourend, $join){
										$query->whereDate('date_time_start', '<=', $fourend)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)								// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($fourend, $join){
												$query->whereDate('date_time_start', '<=', $fourend)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $fourmonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);

			} else {																			// count from $fourend
				$fourm = $fourstart->toPeriod($fourend);										// 23 days
				// dump($fourm->count());														// 23 days
				// dump($fourend);
				$nofiveweekend = $fourstart->diffInWeekdays($fourend, true);					// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$fourholiday = HRHolidayCalendar::where(function (Builder $query) use ($fourstart, $fourend){
														$query->whereDate('date_start', '>=', $fourstart)
														->WhereDate('date_start', '<=', $fourend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($fourholiday) {
					foreach ($fourholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($fourstart, $fourend){
									$query->whereDate('saturday_date', '<=', $fourend)
									->WhereDate('saturday_date', '>=', $fourstart);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($fourstart, $fourend){
											$query->whereDate('attend_date', '>=', $fourstart)
											->whereDate('attend_date', '<=', $fourend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $fourstart to $fourend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($fourstart, $fourend){
											$query->whereDate('attend_date', '>=', $fourstart)
											->whereDate('attend_date', '<=', $fourend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($fourstart, $fourend){
											$query->whereDate('attend_date', '>=', $fourstart)
											->whereDate('attend_date', '<=', $fourend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($fourend, $fourstart){
										$query->whereDate('date_time_start', '<=', $fourend)
										->WhereDate('date_time_end', '>=', $fourstart);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($fourend, $fourstart){
												$query->whereDate('date_time_start', '<=', $fourend)
												->WhereDate('date_time_end', '>=', $fourstart);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $fourmonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $month,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}

		if ($checkmonthsago >= 3) {
			if ($join->gte($threestart)) {													// check if he join in the same month, count from $join
				$threem = $join->toPeriod($threeend);											// 23 days
				// dump($threem->count());													// 23 days
				// dump($threeend);
				$nofiveweekend = $join->diffInWeekdays($threeend);								// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$threeholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $threeend){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $threeend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($threeholiday) {
					foreach ($threeholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $threeend){
									$query->whereDate('saturday_date', '<=', $threeend)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $threeend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $threeend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $threeend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $threeend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $threeend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $threeend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $threeend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($threeend, $join){
										$query->whereDate('date_time_start', '<=', $threeend)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)								// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($threeend, $join){
												$query->whereDate('date_time_start', '<=', $threeend)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $threemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);

			} else {																			// count from $threeend
				$threem = $threestart->toPeriod($threeend);										// 23 days
				// dump($threem->count());														// 23 days
				// dump($threeend);
				$nofiveweekend = $threestart->diffInWeekdays($threeend, true);					// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$threeholiday = HRHolidayCalendar::where(function (Builder $query) use ($threestart, $threeend){
														$query->whereDate('date_start', '>=', $threestart)
														->WhereDate('date_start', '<=', $threeend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($threeholiday) {
					foreach ($threeholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($threestart, $threeend){
									$query->whereDate('saturday_date', '<=', $threeend)
									->WhereDate('saturday_date', '>=', $threestart);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($threestart, $threeend){
											$query->whereDate('attend_date', '>=', $threestart)
											->whereDate('attend_date', '<=', $threeend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $threestart to $threeend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($threestart, $threeend){
											$query->whereDate('attend_date', '>=', $threestart)
											->whereDate('attend_date', '<=', $threeend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($threestart, $threeend){
											$query->whereDate('attend_date', '>=', $threestart)
											->whereDate('attend_date', '<=', $threeend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($threeend, $threestart){
										$query->whereDate('date_time_start', '<=', $threeend)
										->WhereDate('date_time_end', '>=', $threestart);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($threeend, $threestart){
												$query->whereDate('date_time_start', '<=', $threeend)
												->WhereDate('date_time_end', '>=', $threestart);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $threemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $month,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}

		if ($checkmonthsago >= 2) {
			if ($join->gte($twostart)) {													// check if he join in the same month, count from $join
				$twom = $join->toPeriod($twoend);											// 23 days
				// dump($twom->count());													// 23 days
				// dump($twoend);
				$nofiveweekend = $join->diffInWeekdays($twoend);								// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$twoholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $twoend){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $twoend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($twoholiday) {
					foreach ($twoholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $twoend){
									$query->whereDate('saturday_date', '<=', $twoend)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $twoend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $twoend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $twoend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $twoend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $twoend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $twoend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $twoend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($twoend, $join){
										$query->whereDate('date_time_start', '<=', $twoend)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)								// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($twoend, $join){
												$query->whereDate('date_time_start', '<=', $twoend)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $twomonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);

			} else {																			// count from $twoend
				$twom = $twostart->toPeriod($twoend);										// 23 days
				// dump($twom->count());														// 23 days
				// dump($twoend);
				$nofiveweekend = $twostart->diffInWeekdays($twoend, true);					// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$twoholiday = HRHolidayCalendar::where(function (Builder $query) use ($twostart, $twoend){
														$query->whereDate('date_start', '>=', $twostart)
														->WhereDate('date_start', '<=', $twoend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($twoholiday) {
					foreach ($twoholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($twostart, $twoend){
									$query->whereDate('saturday_date', '<=', $twoend)
									->WhereDate('saturday_date', '>=', $twostart);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($twostart, $twoend){
											$query->whereDate('attend_date', '>=', $twostart)
											->whereDate('attend_date', '<=', $twoend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $twostart to $twoend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($twostart, $twoend){
											$query->whereDate('attend_date', '>=', $twostart)
											->whereDate('attend_date', '<=', $twoend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($twostart, $twoend){
											$query->whereDate('attend_date', '>=', $twostart)
											->whereDate('attend_date', '<=', $twoend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($twoend, $twostart){
										$query->whereDate('date_time_start', '<=', $twoend)
										->WhereDate('date_time_end', '>=', $twostart);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($twoend, $twostart){
												$query->whereDate('date_time_start', '<=', $twoend)
												->WhereDate('date_time_end', '>=', $twostart);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $twomonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $month,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}
// dd($checkmonthsago);
		if ($checkmonthsago >= 1) {
			if ($join->gte($onestart)) {													// check if he join in the same month, count from $join
				$onem = $join->toPeriod($oneend);											// 23 days
				// dump($onem->count());													// 23 days
				// dump($oneend);
				$nofiveweekend = $join->diffInWeekdays($oneend);								// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$oneholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $oneend){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $oneend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($oneholiday) {
					foreach ($oneholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $oneend){
									$query->whereDate('saturday_date', '<=', $oneend)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $oneend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $oneend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $oneend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $oneend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $oneend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $oneend){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $oneend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($oneend, $join){
										$query->whereDate('date_time_start', '<=', $oneend)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)								// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($oneend, $join){
												$query->whereDate('date_time_start', '<=', $oneend)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $onemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);

			} else {																			// count from $oneend
				$onem = $onestart->toPeriod($oneend);										// 23 days
				// dump($onem->count());														// 23 days
				// dump($oneend);
				$nofiveweekend = $onestart->diffInWeekdays($oneend, true);					// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$oneholiday = HRHolidayCalendar::where(function (Builder $query) use ($onestart, $oneend){
														$query->whereDate('date_start', '>=', $onestart)
														->WhereDate('date_start', '<=', $oneend);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($oneholiday) {
					foreach ($oneholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($onestart, $oneend){
									$query->whereDate('saturday_date', '<=', $oneend)
									->WhereDate('saturday_date', '>=', $onestart);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($onestart, $oneend){
											$query->whereDate('attend_date', '>=', $onestart)
											->whereDate('attend_date', '<=', $oneend);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $onestart to $oneend only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($onestart, $oneend){
											$query->whereDate('attend_date', '>=', $onestart)
											->whereDate('attend_date', '<=', $oneend);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($onestart, $oneend){
											$query->whereDate('attend_date', '>=', $onestart)
											->whereDate('attend_date', '<=', $oneend);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($oneend, $onestart){
										$query->whereDate('date_time_start', '<=', $oneend)
										->WhereDate('date_time_end', '>=', $onestart);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($oneend, $onestart){
												$query->whereDate('date_time_start', '<=', $oneend)
												->WhereDate('date_time_end', '>=', $onestart);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $onemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $month,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}

		if ($checkmonthsago >= 0) {
			if ($join->gte($nowstartmonth)) {													// check if he join in the same month, count from $join
				$onem = $join->toPeriod($now);											// 23 days
				// dump($onem->count());													// 23 days
				// dump($now);
				$nofiveweekend = $join->diffInWeekdays($now);								// get weekdays from above as we have only sunday as a weekend
				// dump($nofiveweekend + 1);													// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$oneholiday = HRHolidayCalendar::where(function (Builder $query) use ($join, $now){
														$query->whereDate('date_start', '>=', $join)
														->WhereDate('date_start', '<=', $now);
													})
													->get();
													// ->ddRawSql();
				$q = 0;
				if ($oneholiday) {
					foreach ($oneholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($join, $now){
									$query->whereDate('saturday_date', '<=', $now)
									->WhereDate('saturday_date', '>=', $join);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($join, $now){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $now);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);														// from $join to $now only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}
				// dump($sat);

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($join, $now){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $now);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($join, $now){
											$query->whereDate('attend_date', '>=', $join)
											->whereDate('attend_date', '<=', $now);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($now, $join){
										$query->whereDate('date_time_start', '<=', $now)
										->WhereDate('date_time_end', '>=', $join);
								})
								->get();
								// ->ddRawSql();
				// dump($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)								// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($now, $join){
												$query->whereDate('date_time_start', '<=', $now)
												->WhereDate('date_time_end', '>=', $join);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $monthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);

			} else {																		// count from $now
				// dd($nowstartmonth.' date start month');
				$onem = $nowstartmonth->copy()->toPeriod($now);								// 23 days
				// dump($onem->count().' days');											// 23 days
				// dump($now->copy()->startOfMonth().' start of month');
				$nofiveweekend = $nowstartmonth->diffInWeekdays($now, true);				// get weekdays from above as we have only sunday as a weekend
				// dd(($nofiveweekend + 1).' days without weekend');						// 19 days : need to plus 1 for correct answer so it will be 20 days

				// getting holiday on that month
				$oneholiday = HRHolidayCalendar::where(function (Builder $query) use ($nowstartmonth, $now){
														$query->whereDate('date_start', '>=', $nowstartmonth)
														->whereDate('date_start', '<=', $now);
													})
													->get();
													// ->ddRawSql();
				// dd($oneholiday);
				$q = 0;
				if ($oneholiday) {
					foreach ($oneholiday as $v) {
						$sixperiod = Carbon::parse($v->date_start)->daysUntil($v->date_end, 1);			// 5 days
						foreach ($sixperiod as $val) {
							if (Carbon::parse($val)->dayOfWeek != Carbon::SUNDAY) {
								$q++;
							}
						}
					}
				}

				// saturday, probably this 1 could be a culprit because in the beginning, usually HR does not set restday_group_id, must check on attendance also.
				$satoff = $st->belongstorestdaygroup?->hasmanyrestdaycalendar()				// getting sat for staff, if null than only 26 days available for him, otherwise, its lower than that.
							->where(function (Builder $query) use ($nowstartmonth, $now){
									$query->whereDate('saturday_date', '<=', $now)
									->WhereDate('saturday_date', '>=', $nowstartmonth);
							})
							->get()->count();
							// ->ddRawSql();
				// dump($satoff);																// restday group 1

				// getting saturday working
				$saturdayatt = HRAttendance::where('staff_id', $st->id)
										->where(function (Builder $query) use ($nowstartmonth, $now){
											$query->whereDate('attend_date', '>=', $nowstartmonth)
											->whereDate('attend_date', '<=', $now);
										})
										->where(function (Builder $query){
											$query->whereRaw('DAYOFWEEK(hr_attendances.attend_date) = 7')
											->where('daytype_id', 2);
										})
										->get()->count();
										// ->ddRawSql();
				// dump($saturdayatt);															// from $nowstartmonth to $now only on 11/3 he is RESTDAY
				// need to get the most working days
				if($satoff >= $saturdayatt) {												// meaning: according to attendance at that time, its not set yet
					$sat = $saturdayatt;													// choose the smallest sat count which means more working days
				} elseif ($satoff == $saturdayatt) {
					$sat = $satoff;
				}

				// getting absent
				$fullabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 1)
										->where(function (Builder $query) use ($nowstartmonth, $now){
											$query->whereDate('attend_date', '>=', $nowstartmonth)
											->whereDate('attend_date', '<=', $now);
										})
										->get();
										// ->ddRawSql();
				$u = 0;
				if ($fullabsent->count()) {
					foreach ($fullabsent as $v) {
						$u++;
					}
				}
				// dump($u);

				$halfabsent = HRAttendance::where('staff_id', $st->id)
										->where('attendance_type_id', 2)
										->where(function (Builder $query) use ($nowstartmonth, $now){
											$query->whereDate('attend_date', '>=', $nowstartmonth)
											->whereDate('attend_date', '<=', $now);
										})
										->get();
										// ->ddRawSql();
				$p = 0;
				if ($halfabsent->count()) {
					foreach ($halfabsent as $v) {
						$p += 0.5;
					}
				}
				// dump($p);

				// getting full day leave for that months
				$fulldayleave = HRLeave::where('staff_id', $st->id)							// get period from here
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
								->where(function (Builder $query) use ($now, $nowstartmonth){
										$query->whereDate('date_time_start', '<=', $now)
										->WhereDate('date_time_end', '>=', $nowstartmonth);
								})
								->get();
								// ->ddRawSql();
				// dd($fulldayleave);
				$i = 0;
				foreach ($fulldayleave as $v) {
					$i += $v->period_day;
				}
				// dump($i);

				// getting half day leave
				$halfdayleave = HRLeave::where('staff_id', $st->id)							// get period from here
										->where(function (Builder $query){
											$query->where('leave_type_id', '<>', 9)
											->where('half_type_id', 2);
										})
										->where(function (Builder $query) use ($now, $nowstartmonth){
												$query->whereDate('date_time_start', '<=', $now)
												->WhereDate('date_time_end', '>=', $nowstartmonth);
										})
										->where(function (Builder $query){
											$query->whereIn('leave_status_id', [5,6])
												->orWhereNull('leave_status_id');
										})
										->get();
										// ->ddRawSql();

				$r = 0;
				foreach ($halfdayleave as $v) {
					$r += $v->period_day;
				}
				// dump($r);

				// start counting for this month
				$month = $onemonthname;
				$workday = ($nofiveweekend + 1) - $sat - $q;
				// dump($workday);
				$absent = $u + $p;
				// dump($absent);
				$leave = $i + $r;
				// dump($leave);

				$attpercentage = number_format( ($workday - $absent - $leave) / ($workday) * 100, 2);
				// dump($attpercentage);
			}
			$chartdata[] = [
								'month' => $monthname,
								'percentage' => $attpercentage,
								'workdays' => $workday,
								'leaves' => $leave,
								'absents' => $absent,
								'working_days' => ($workday - $absent - $leave),
							];
		}
		return response()->json($chartdata);
	}

	public function staffpercentage(Request $request)/*: JsonResponse*/
	{
		$st = Staff::find($request->id);					// need to check date join

		$soy = now()->copy()->startOfYear();				// early this year
		$lsoy = $soy->copy()->subYear();					// early last year
		// dd($lsoy);
		// dd($lsoy->diffInMonths(now()));

		for ($i = 0; $i <= $lsoy->diffInMonths(now()); $i++) {// take only 2 years back
			$sm = $lsoy->copy()->addMonth($i);
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

					$absent = $s->where('attendance_type_id', 1)->whereDate('attend_date', $s->attend_date)->where('daytype_id', 1)->where('staff_id', $st->id)->get();
					$a += $absent->count();
					// dump($absent.' absent');
				}
			} else {
				$workday = 1;
				$fdl = 1;
			}
			$percentage = (($workday - $fdl - $a) / $workday) * 100;

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

}
