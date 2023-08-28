<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load model
use App\Models\Setting;

use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveEntitlement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;

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

		if(\App\Models\Setting::find(4)->first()->active == 1){		// 3days checking
			$lusa1 = Carbon::now()->addDays(\App\Models\Setting::find(5)->first()->active + 1)->format('Y-m-d');
			$period2 = \Carbon\CarbonPeriod::create(Carbon::now()->format('Y-m-d'), '1 days', $lusa1);
			$lusa = [];
			foreach ($period2 as $key1) {
				$lusa[] = $key1->format('Y-m-d');
			}
		} else {
			$lusa = [];
		}

		if($request->type == 1){
			$unavailableleave = Arr::collapse([$blockdate, $lusa]);
		} elseif($request->type == 2) {
			$unavailableleave = $blockdate;
		}
		return response()->json($unavailableleave);
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
		$au = OptRestdayGroup::all();
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

	public function authorise()
	{
		// https://select2.org/data-sources/formats
		$au = OptAuthorise::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->authorise,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function branch()
	{
		// https://select2.org/data-sources/formats
		$au = OptBranch::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->location,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function country()
	{
		// https://select2.org/data-sources/formats
		$au = OptCountry::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->country,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function division()
	{
		// https://select2.org/data-sources/formats
		$au = OptDivision::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->div,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function educationlevel()
	{
		// https://select2.org/data-sources/formats
		$au = OptEducationLevel::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->education_level,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function gender()
	{
		// https://select2.org/data-sources/formats
		$au = OptGender::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->gender,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function status()
	{
		// https://select2.org/data-sources/formats
		$au = OptStatus::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->status.' | '.$key->code,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function category()
	{
		// https://select2.org/data-sources/formats
		$au = OptCategory::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->category,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function healthstatus()
	{
		// https://select2.org/data-sources/formats
		$au = OptHealthStatus::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->health_status,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function maritalstatus()
	{
		// https://select2.org/data-sources/formats
		$au = OptMaritalStatus::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->marital_status,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function race()
	{
		// https://select2.org/data-sources/formats
		$au = OptRace::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->race,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function religion()
	{
		// https://select2.org/data-sources/formats
		$au = OptReligion::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->religion,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function taxexemptionpercentage()
	{
		// https://select2.org/data-sources/formats
		$au = OptTaxExemptionPercentage::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->tax_exemption_percentage,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}

	public function relationship()
	{
		// https://select2.org/data-sources/formats
		$au = OptRelationship::all();
		foreach ($au as $key) {
			$cuti['results'][] = [
									'id' => $key->id,
									'text' => $key->relationship,
								];
			// $cuti['pagination'] = ['more' => true];
		}
		return response()->json( $cuti );
	}










}
