<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load model
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptLeaveType;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\Setting;
use App\Models\HumanResources\OptWorkingHour;
use App\Models\HumanResources\HRLeaveEntitlement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\OptLeaveStatus;

// load custom helper
use App\Helpers\UnavailableDateTime;
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Session;

class AjaxController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
	}

	// cancel leave
	public function leavecancel(Request $request, HRLeave $hrleave)
	{
		if($request->cancel == 3)
		{
			// all of the debugging echo need to be commented out if using ajax.
			// cari leave type dulu
			$n = HRLeave::find($request->id);
			// echo $n.' staff Leave model<br />';
			//////////////////////////////////////////////////////////////////////////////////////////////
			// jom cari leave type, jenis yg boleh tolak shj : al, mc, el-al, el-mc, nrl, ml
			// echo $n->leave_type_id.' leave type<br />';

			$dts = \Carbon\Carbon::parse( $n->date_time_start );
			$now = \Carbon\Carbon::now();

			// leave deduct from AL or EL-AL
			// make sure to cancel at the approver also #####################################################################
			if ( $n->leave_type_id == 1 || $n->leave_type_id == 5 ) {
				// cari al dari staffleave dan tambah balik masuk dalam hasmanyleaveannual

				// cari period cuti
				// echo $n->period_day.' period cuti<br />';

				// cari al dari staff, year yg sama dgn date apply cuti.
				// echo $n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->first()->annual_leave_balance.' applicant annual leave balance<br />';

				$addl = $n->period_day + $n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->first()->annual_leave_balance;
				$addu = $n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->first()->annual_leave_utilize - $n->period_day;
				// echo $addl.' masukkan dalam annual balance<br />';

				// update the al balance
				$n->belongstostaff->hasmanyleaveannual()->where('year', $dts->format('Y'))->update([
					'annual_leave_balance' => $addl,
					'annual_leave_utilize' => $addu,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name.' reference hr_leaves.id'.$request->id
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
			}

			if( $n->leave_type_id == 2 ) { // leave deduct from MC
				// sama lebih kurang AL mcm kat atas. so....
				$addl = $n->period_day + $n->belongstostaff->hasmanyleavemc()->where('year', $dts->format('Y'))->first()->mc_leave_balance;
				$addu = $n->belongstostaff->hasmanyleavemc()->where('year', $dts->format('Y'))->first()->mc_leave_utilize - $n->period_day;
				// update the mc balance
				$n->belongstostaff->hasmanyleavemc()->where('year', $dts->format('Y'))->update([
					'mc_leave_balance' => $addl,
					'mc_leave_utilize' => $addu,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
			}

			if( $n->leave_type_id == 3 || $n->leave_type_id == 6 || $n->leave_type_id == 11  || $n->leave_type_id == 12 ) { // leave deduct from UPL, EL-UPL, MC-UPL & S-UPL
				// echo 'leave deduct from UPL<br />';

				// process a bit different from al and mc
				// we can ignore all the data in hasmanyleaveentitlement mode. just take care all the things in staff leaves only.
				// make period 0 again, regardsless of the ttotal period and then update as al and mc.
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
				// update status for all approval
			}

			if( $n->leave_type_id == 4 || $n->leave_type_id == 10 ) { // leave deduct from NRL & EL-NRL
				// echo 'leave deduct from NRL<br />';

				// cari period cuti
				// echo $n->period_day.' period cuti<br />';

				// echo $n->hasmanyleavereplacement()->first().' staffleavereplacement model<br />';
				// hati2 pasai ada 2 kes dgn period, full and half day
				// kena update balik di staffleavereplacement model utk return back period.
				// period campur balik dgn leave utilize (2 table berbeza)
				// echo $n->hasmanyleavereplacement()->first()->leave_utilize.' leave utilize<br />';
				// echo $n->hasmanyleavereplacement()->first()->leave_total.' leave total<br />';

				// untuk update di column leave_balance
				$addr = $n->belongstomanyleavereplacement()->first()->leave_balance + $n->period_day;
				$addru = $n->belongstomanyleavereplacement()->first()->leave_utilize - $n->period_day;
				// echo $addr.' untuk update kat column staff_leave_replacement.leave_utilize<br />';

				// update di table staffleavereplacement. remarks kata sapa reject
				$n->belongstomanyleavereplacement()->first()->update([
					// 'leave_type_id' => NULL,
					'leave_balance' => $addr,
					'leave_utilize' => $addru,
					'remarks' => 'Cancelled by '.\Auth::user()->belongstostaff->name
				]);
				// update di table staff leave pulokk staffleave
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
			}

			if( $n->leave_type_id == 7 ) { // leave deduct from ML
				// echo 'leave deduct from ML<br />';

				// lebih kurang sama dengan al atau mc, maka..... :) copy paste
				// cari period cuti
				// echo $n->period.' period cuti<br />';

				// cari al dari applicant, year yg sama dgn date apply cuti.
				// echo $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_leave_balance.' applicant maternity leave balance<br />';

				$addl = $n->period_day + $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_balance;
				// echo $addl.' masukkan dalam annual balance<br />';

				// find all approval
				// echo $n->hasmanystaffapproval()->get().'find all approval<br />';

				// echo \Auth::user()->belongstostaff->belongtomanyposition()->wherePivot('main', 1)->first()->position.' position <br />';
				// echo \Auth::user()->belongstostaff->name.' position <br />';

				// update the al balance
				$n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->update([
					'maternity_balance' => $addl,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
			}

			if( $n->leave_type_id == 9 ) { // leave deduct from Time Off
				// echo 'leave deduct from TF<br />';

				// dekat dekat nak sama dgn UPL, maka... :P copy paste

				// process a bit different from al and mc
				// we can ignore all the data in staffannualmcmaternity mode. just take care all the things in staff leaves only.
				// make period 0 again, regardsless of the ttotal period and then update as al and mc.
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_time' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name]);
			}
			// finally update at all the approver according to his/her leave flow
			if($n->belongstostaff->belongstoleaveapprovalflow->backup_approval == 1) {
				$n->hasoneleaveapprovalbackup()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow->supervisor_approval == 1) {
				$n->hasoneleaveapprovalsupervisor()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow->hod_approval == 1) {
				$n->hasoneleaveapprovalhod()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow->director_approval == 1) {
				$n->hasoneleaveapprovaldir()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			if($n->belongstostaff->belongstoleaveapprovalflow->hr_approval == 1) {
				$n->hasoneleaveapprovalhr()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name
				]);
			}
			//////////////////////////////////////////////////////////////////////////////////////////////
			// done processing the data
			return response()->json([
				'status' => 'success',
				'message' => 'Your leave has been cancelled.',
			]);
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
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

	public function leaverapprove(HRLeaveApprovalBackup $hrleaveapprovalbackup)
	{
		$hrleaveapprovalbackup->update(['leave_status_id' => 5]);
			return response()->json([
				'status' => 'success',
				'message' => 'Your colleague leave has been approved... and he/she says thank you.',
			]);
	}

	public function leavesapprove(Request $request, HRLeaveApprovalSupervisor $hrleaveapprovalsupervisor)
	{
		$hrleaveapprovalsupervisor->update(['leave_status_id' => $request->id]);
			return response()->json([
				'status' => 'success',
				'message' => 'Leave has been approved.',
			]);
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
			$ls['results'][] = ['id' => $v->id, 'text' => $v->status];
		}
		return response()->json($ls);
	}

	public function supervisorstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave__status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalSupervisor::find($request->id);
		$sal = $sa->belongstostaffleave;										// this supervisor approval belongs to leave
		$sauser = $sal->belongstostaff;											// leave belongs to user, not authuser anymore
		// dd($sauser);
		$vc = $sal->verify_code;
		// dd($sal);
		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->route('leave.index')->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 2) {												// mc leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Supervisor ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->route('leave.index');
	}

	public function hodstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave__status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalHOD::find($request->id);
		$sal = $sa->belongstostaffleave;										// this hod approval belongs to leave
		$sauser = $sal->belongstostaff;											// leave belongs to user, not authuser anymore
		// dd($sauser);
		$vc = $sal->verify_code;
		// dd($sal);
		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->route('leave.index')->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 2) {												// mc leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by HOD ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->route('leave.index');
	}

	public function dirstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave__status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalDirector::find($request->id);
		$sal = $sa->belongstostaffleave;													// this hod approval belongs to leave
		$sauser = $sal->belongstostaff;												// leave belongs to user, not authuser anymore
		// dd($sauser);
		$vc = $sal->verify_code;
		// dd($sal);
		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->route('leave.index')->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 2) {												// mc leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
		} elseif($request->leave_status_id == 6) {								// leave waived, so need to put back all leave period.
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 2) {												// mc leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->route('leave.index');
	}

	public function hrstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave__status_id.required' => 'Please choose your approval',
				'verify_code.required_if' => 'Please insert :attribute to approve leave, otherwise it wont be necessary for leave application reject',
			],
			[
				'leave_status_id' => 'Approval Status',
				'verify_code' => 'Verification Code'
			]
		);

		// get verify code
		$sa = HRLeaveApprovalHR::find($request->id);
		$sal = $sa->belongstostaffleave;											// this hr approval belongs to leave
		$sauser = $sal->belongstostaff;												// leave belongs to user, not authuser anymore
		// dd($sauser);
		$vc = $sal->verify_code;
		// dd($sal);
		if( $request->leave_status_id == 5 ) {									// leave approve
			if($vc == $request->verify_code) {
				$sa->update([
					'staff_id' => \Auth::user()->belongstostaff->id,
					'leave_status_id' => $request->leave_status_id
				]);
			} else {
				Session::flash('flash_message', 'Verification Code was incorrect');
				return redirect()->route('leave.index')->withInput();
			}
		} elseif($request->leave_status_id == 4) {								// leave rejected
			$saly = $sal->leave_type_id;										// need to find out leave type
			if ($saly == 1 || $saly == 5) {										// annual leave: put period leave to annual leave entitlement
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleaveannual->first();				// get annual leave
				$albal = $sala->annual_leave_balance + $pd;						// annual leave balance
				$aluti = $sala->annual_leave_utilize - $pd;						// annual leave utilize
				$sala->update(['annual_leave_balance' => $albal, 'annual_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 4 || $saly == 10) {								// replacement leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavereplacement->first();			// get replacement leave
				$albal = $sala->leave_balance + $pd;							// replacement leave balance
				$aluti = $sala->leave_utilize - $pd;							// replacement leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 2) {												// mc leave
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavemc->first();					// get mc leave
				$albal = $sala->mc_leave_balance + $pd;							// mc leave balance
				$aluti = $sala->mc_leave_utilize - $pd;							// mc leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['leave_balance' => $albal, 'leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 3 || $saly == 6 || $saly == 11 || $saly == 12) {
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 9) {
				$sal->update(['period_time' => 0, 'leave_status_id' => $request->leave_status_id]);
			}

			if($sauser->belongstoleaveapprovalflow->backup_approval == 1){																// update on backup
				$sal->hasmanyleaveapprovalbackup()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->supervisor_approval == 1){															// update on supervisor
				$sal->hasmanyleaveapprovalsupervisor()->update(['staff_id' => \Auth::user()->belongstostaff->id, 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hod_approval == 1){																	// update on hod
				$sal->hasmanyleaveapprovalhod()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->director_approval == 1){															// update on director
				$sal->hasmanyleaveapprovaldir()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
			if($sauser->belongstoleaveapprovalflow->hr_approval == 1){																	// update on hr
				$sal->hasmanyleaveapprovalhr()->update([/*'staff_id' => \Auth::user()->belongstostaff->id,*/ 'leave_status_id' => $request->leave_status_id, 'remarks' => 'Rejected by Director ('.\Auth::user()->belongstostaff->name.') on '.\Carbon\Carbon::now()->format('j M Y g:i a')]);
			}
		} elseif($request->leave_status_id == 6) {								// leave waived, so need to put back all leave period.

		}
		Session::flash('flash_message', 'Successfully make an approval for user.');
		return redirect()->route('leave.index');
	}

}
