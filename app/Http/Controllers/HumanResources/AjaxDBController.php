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
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Session;

class AjaxDBController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	// compared username
	public function loginuser(Request $request)
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

	// get types of leave according to user
	public function leaveType(Request $request)
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

	public function unavailabledate(Request $request)
	{
		$blockdate = UnavailableDateTime::blockDate(\Auth::user()->belongstostaff->id);

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

	public function unblockhalfdayleave(Request $request)
	{
		$blocktime = UnavailableDateTime::unblockhalfdayleave($request->id);
		return response()->json($blocktime);
	}

	public function backupperson(Request $request)
	{
		// we r going to find a backup person
		// 1st, we need to take a look into his/her department.
		$user = \Auth::user()->belongstostaff;
		$dept = $user->belongstomanydepartment()->first();
		$userindept = $dept->belongstomanystaff()->where('active', 1)->get();

		// backup from own department if he/she have
		// https://select2.org/data-sources/formats
		$backup['results'][] = [];
		if ($userindept) {
			foreach($userindept as $key){
				if($key->id != \Auth::user()->belongstostaff->id){
					$backup['results'][] = [
											'id' => $key->id,
											'text' => $key->name,
										];
				}
			}
		}

		$crossbacku = $user->crossbackupto()?->wherePivot('active', 1)->get();
		$crossbackup['results'][] = [];
		if($crossbacku) {
			foreach($crossbacku as $key){
				$crossbackup['results'][] = [
												'id' => $key->id,
												'text' => $key->name,
											];
			}
		}
		// dd($crossbackup);
		// $allbackups = Arr::collapse([$backup, $crossbackup]);
		$allbackups = array_merge_recursive($backup, $crossbackup);
		return response()->json( $allbackups );
	}

	public function timeleave(Request $request)
	{
		$whtime = UnavailableDateTime::workinghourtime($request->date, $request->id);
		return response()->json($whtime->first());
	}

	public function leavestatus(Request $request)
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

	public function staffcrossbackup(Request $request)
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

	public function department(Request $request)
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

	public function restdaygroup(Request $request)
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

	public function authorise(Request $request)
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

	public function branch(Request $request)
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

	public function country(Request $request)
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

	public function educationlevel(Request $request)
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

	public function gender(Request $request)
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

	public function status(Request $request)
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

	public function category(Request $request)
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

	public function healthstatus(Request $request)
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

	public function maritalstatus(Request $request)
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

	public function race(Request $request)
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

	public function religion(Request $request)
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

	public function taxexemptionpercentage(Request $request)
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

	public function relationship(Request $request)
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

	public function division(Request $request)
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

	public function leaveevents(Request $request)
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

	public function staffattendance(Request $request)/*: JsonResponse*/
	{
		// this is for fullcalendar, its NOT INCLUSIVE for the last date
		// get the attandence 1st
		$attendance = HRAttendance::where('staff_id', $request->staff_id)->get();
		foreach ($attendance as $s) {

		}

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
		$sat = Staff::find($request->staff_id)->belongstorestdaygroup->hasmanyrestdaycalendar()->get();
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
				where(function (Builder $query) use ($s){
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

	public function staffattendancelist(Request $request)
	{
		$sa = \App\Models\HumanResources\HRAttendance::select('staff_id')
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




}
