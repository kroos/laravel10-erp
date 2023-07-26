<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load model
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptLeaveType;
use App\Models\HumanResources\HRHolidayCalendar;;
use App\Models\Setting;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
// use Session;

class AjaxController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
	}

	// cancel leave
	public function update(Request $request, HRLeave $hrleave)
	{
		if($request->cancel == 3)
		{
			// all of the debugging echo need to be commented out if using ajax.
			// cari leave type dulu
			$n = HRLeave::find($request->id);
			echo $n.' staff Leave model<br />';
			//////////////////////////////////////////////////////////////////////////////////////////////
			// jom cari leave type, jenis yg boleh tolak shj : al, mc, el-al, el-mc, nrl, ml
			// echo $n->leave_type_id.' leave type<br />';

			$dts = \Carbon\Carbon::parse( $n->date_time_start );
			$now = \Carbon\Carbon::now();

			// leave deduct from AL or EL-AL
			// make sure to cancel at the approver also #####################################################################
			if ( $n->leave_type_id == 1 || $n->leave_type_id == 5 ) {
				// cari al dari staffleave dan tambah balik masuk dalam hasmanyleaveentitlement

				// cari period cuti
				// echo $n->period_day.' period cuti<br />';

				// cari al dari staff, year yg sama dgn date apply cuti.
				// echo $n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->first()->al_balance.' applicant annual leave balance<br />';

				$addl = $n->period_day + $n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->first()->al_balance;
				// echo $addl.' masukkan dalam annual balance<br />';

				// update the al balance
				$n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->update([
					'al_balance' => $addl,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name.' reference hr_leaves.id'.$request->id
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name]);

				// update also for approver part
			}

			if( $n->leave_id == 2 || $n->leave_id == 11 ) { // leave deduct from MC or MC-UPL
				// sama lebih kurang AL mcm kat atas. so....
				$addl = $n->period_day + $n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->first()->mc_balance;
				// update the mc balance
				$n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->update([
					'mc_balance' => $addl,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name]);
			}

			if( $n->leave_id == 3 || $n->leave_id == 6 ) { // leave deduct from UPL or EL-UPL
				// echo 'leave deduct from UPL<br />';

				// process a bit different from al and mc
				// we can ignore all the data in hasmanyleaveentitlement mode. just take care all the things in staff leaves only.
				// make period 0 again, regardsless of the ttotal period and then update as al and mc.
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name]);
				// update status for all approval
			}

			if( $n->leave_id == 4 ) { // leave deduct from NRL
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
				$addr = $n->hasmanyleavereplacement()->first()->leave_total - $n->period_day;
				// echo $addr.' untuk update kat column staff_leave_replacement.leave_utilize<br />';

				// update di table staffleavereplcaement. remarks kata sapa reject
				$n->hasmanyleavereplacement()->update([
					'leave_id' => NULL,
					'leave_balance' => $n->period_day,
					'leave_utilize' => $addr,
					'remarks' => 'Cancelled by '.\Auth::user()->belongtostaff->name
				]);
				// update di table staff leave pulokk staffleave
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name]);
			}

			if( $n->leave_id == 7 ) { // leave deduct from ML
				// echo 'leave deduct from ML<br />';

				// lebih kurang sama dengan al atau mc, maka..... :) copy paste
				// cari period cuti
				// echo $n->period.' period cuti<br />';

				// cari al dari applicant, year yg sama dgn date apply cuti.
				// echo $n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->first()->maternity_leave_balance.' applicant maternity leave balance<br />';

				$addl = $n->period + $n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->first()->maternity_balance;
				// echo $addl.' masukkan dalam annual balance<br />';

				// find all approval
				// echo $n->hasmanystaffapproval()->get().'find all approval<br />';

				// echo \Auth::user()->belongtostaff->belongtomanyposition()->wherePivot('main', 1)->first()->position.' position <br />';
				// echo \Auth::user()->belongtostaff->name.' position <br />';

				// update the al balance
				$n->belongtostaff->hasmanyleaveentitlement()->where('year', $dts->format('Y'))->update([
					'maternity_balance' => $addl,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
				]);
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_day' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name]);
			}

			if( $n->leave_id == 9 ) { // leave deduct from Time Off
				// echo 'leave deduct from TF<br />';

				// dekat dekat nak sama dgn UPL, maka... :P copy paste

				// process a bit different from al and mc
				// we can ignore all the data in staffannualmcmaternity mode. just take care all the things in staff leaves only.
				// make period 0 again, regardsless of the ttotal period and then update as al and mc.
				// update period, status leave of the applicant. status close by HOD/supervisor
				$n->update(['period_time' => 0, 'leave_status_id' => 3, 'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name]);
			}
			// finally update at all the approver according to his/her leave flow
			if($n->belongtostaff->belongstoleaveapprovalflow->backup_approval == 1) {
				$n->hasoneleaveapprovalbackup()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
				]);
			}
			if($n->belongtostaff->belongstoleaveapprovalflow->supervisor_approval == 1) {
				$n->hasoneleaveapprovalsupervisor()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
				]);
			}
			if($n->belongtostaff->belongstoleaveapprovalflow->hod_approval == 1) {
				$n->hasoneleaveapprovalhod()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
				]);
			}
			if($n->belongtostaff->belongstoleaveapprovalflow->director_approval == 1) {
				$n->hasoneleaveapprovaldir()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
				]);
			}
			if($n->belongtostaff->belongstoleaveapprovalflow->hr_approval == 1) {
				$n->hasoneleaveapprovalhr()->update([
					'leave_status_id' => 3,
					'remarks' => 'Cancelled By '.\Auth::user()->belongtostaff->name
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
		$leaveALMC = $user->hasmanyleaveentitlement()->where('year', date('Y'))->first();
		// cari kalau ada replacement leave
		$oi = $user->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->whereYear('date', date('Y'))->get();

		// dd($oi->sum('leave_balance'));

		if(Setting::where('id', 3)->first()->active == 1){				// special unpaid leave activated
			if($user->gender_id == 1){									// laki
				if($oi->sum('leave_balance') < 1){						// laki | no nrl
					if($leaveALMC->al_balance < 1){						// laki | no nrl | no al
						if($leaveALMC->mc_balance < 1){					// laki | no nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,6,9,12])->get();
						} else {										// laki | no nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,6,9,11,12])->get();
						}
					} else {											// laki | no nrl | al
						if($leaveALMC->mc_balance < 1){					// laki | no nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,5,9,12])->get();
						} else {										// laki | no nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,5,9,11,12])->get();
						}
					}
				} else {												// laki | nrl
					if($leaveALMC->al_balance < 1){						// laki | nrl | no al
						if($leaveALMC->mc_balance < 1){					// laki | nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,4,6,9,10,12])->get();
						} else {										// laki | nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,4,6,9,10,11,12])->get();
						}
					} else {											// laki | nrl | al
						if($leaveALMC->mc_balance < 1){					// laki | nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,4,5,9,10,12])->get();
						} else {										// laki | nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,4,5,9,10,11,12])->get();
						}
					}
				}
			} else {													// pempuan
				if($oi->sum('leave_balance') < 1){						// pempuan | no nrl
					if($leaveALMC->al_balance < 1){						// pempuan | no nrl | no al
						if($leaveALMC->mc_balance < 1){					// pempuan | no nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,6,7,9,12])->get();
						} else {										// pempuan | no nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,6,7,9,11,12])->get();
						}
					} else {											// pempuan | no nrl | al
						if($leaveALMC->mc_balance < 1){					// pempuan | no nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,5,7,9,12])->get();
						} else {										// pempuan | no nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,5,7,9,11,12])->get();
						}
					}
				} else {												// pempuan | nrl
					if($leaveALMC->al_balance < 1){						// pempuan | nrl | no al
						if($leaveALMC->mc_balance < 1){					// pempuan | nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,4,6,7,9,10,12])->get();
						} else {										// pempuan | nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,4,6,7,9,10,11,12])->get();
						}
					} else {											// pempuan | nrl | al
						if($leaveALMC->mc_balance < 1){					// pempuan | nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,4,5,7,9,10,12])->get();
						} else {										// pempuan | nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,4,5,7,9,10,11,12])->get();
						}
					}
				}
			}
		} else {														// special unpaid leave deactivated
			if($user->gender_id == 1){									// laki
				if($oi->sum('leave_balance') < 1){						// laki | no nrl
					if($leaveALMC->al_balance < 1){						// laki | no nrl | no al
						if($leaveALMC->mc_balance < 1){					// laki | no nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,6,9])->get();
						} else {										// laki | no nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,6,9,11])->get();
						}
					} else {											// laki | no nrl | al
						if($leaveALMC->mc_balance < 1){					// laki | no nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,5,9])->get();
						} else {										// laki | no nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,5,9,11])->get();
						}
					}
				} else {												// laki | nrl
					if($leaveALMC->al_balance < 1){						// laki | nrl | no al
						if($leaveALMC->mc_balance < 1){					// laki | nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,4,6,9,10])->get();
						} else {										// laki | nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,4,6,9,10,11])->get();
						}
					} else {											// laki | nrl | al
						if($leaveALMC->mc_balance < 1){					// laki | nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,4,5,9,10])->get();
						} else {										// laki | nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,4,5,9,10,11])->get();
						}
					}
				}
			} else {													// pempuan
				if($oi->sum('leave_balance') < 1){						// pempuan | no nrl
					if($leaveALMC->al_balance < 1){						// pempuan | no nrl | no al
						if($leaveALMC->mc_balance < 1){					// pempuan | no nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,6,7,9])->get();
						} else {										// pempuan | no nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,6,7,9,11])->get();
						}
					} else {											// pempuan | no nrl | al
						if($leaveALMC->mc_balance < 1){					// pempuan | no nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,5,7,9])->get();
						} else {										// pempuan | no nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,5,7,9,11])->get();
						}
					}
				} else {												// pempuan | nrl
					if($leaveALMC->al_balance < 1){						// pempuan | nrl | no al
						if($leaveALMC->mc_balance < 1){					// pempuan | nrl | no al | no mc
							$er = OptLeaveType::whereIn('id', [3,4,6,7,9,10])->get();
						} else {										// pempuan | nrl | no al | mc
							$er = OptLeaveType::whereIn('id', [2,3,4,6,7,9,10,11])->get();
						}
					} else {											// pempuan | nrl | al
						if($leaveALMC->mc_balance < 1){					// pempuan | nrl | al | no mc
							$er = OptLeaveType::whereIn('id', [1,4,5,7,9,10])->get();
						} else {										// pempuan | nrl | al | mc
							$er = OptLeaveType::whereIn('id', [1,2,4,5,7,9,10,11])->get();
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
		// globally mark date for weekend and holiday as unavailable to choose
		// 1st, check what year is now and disable every public holiday on that year
		$d = Carbon::now(config('app.timezone'));
		// echo $d->year;					// this year
		// echo $d->addYear()->year;		// next year

		// list all holiday date based on this year and next year
		$hdate = HRHolidayCalendar::whereRaw( '"'.$d->copy()->year.'" BETWEEN YEAR(date_start) AND YEAR(date_end)' )->orwhereRaw( '"'.$d->copy()->addYear()->year.'" BETWEEN YEAR(date_start) AND YEAR(date_end)' )->get();
		// dd($hdate);
		$holiday = [];
		foreach ($hdate as $nda) {
			$period = \Carbon\CarbonPeriod::create($nda->date_start, '1 days', $nda->date_end);
			foreach ($period as $key) {
				// echo 'moment("'.$key->format('Y-m-d').'"),';
				$holiday[] = $key->format('Y-m-d');
			}
		}

		// block every sunday
		$today = Carbon::now();
		$start_date = Carbon::create($today->year, 1, 1);
		$end_date = Carbon::create($today->year + 1, 1, 1);
		$sundays = [];
		foreach ($start_date->daysUntil($end_date) as $date) {
			if ($date->dayOfWeek === Carbon::SUNDAY) {
				$sundays[] = $date->format('Y-m-d');
			}
		}

		// block saturday according to group
		// make sure $request->id comes from table staff
		// ->whereYear('saturday_date', $d->copy()->year)->whereYear('saturday_date', $d->copy()->addYear()->year)->get()
		$sat = \App\Models\Staff::find($request->id)?->belongstorestdaygroup()->first()->hasmanyrestdaycalendar()->whereRaw('(YEAR (`saturday_date`) = '.$d->copy()->year.' Or YEAR ( `saturday_date` ) = '.$d->copy()->addYear()->year.')')->get();
		// dd($sat);
		if(!is_null($sat)) {
			$saturdays = [];
			foreach ($sat as $key) {
				$saturdays[] = $key->saturday_date;
			}
		} else {
			$saturdays = [];
		}

		// double date checking
		if(Setting::find(1)->active == 1) {
			// block self leave
			// make sure $request->id comes from table staff
			$leaveday = \App\Models\HumanResources\HRLeave::where('staff_id', $request->id)->/*whereNull('leave_status_id')->*/whereIn('leave_status_id', [5,6,NULL])->whereRaw('"'.$d->copy()->year.'" BETWEEN YEAR(date_time_start) AND YEAR(date_time_end)')->orwhereRaw('"'.$d->copy()->addYear()->year.'" BETWEEN YEAR(date_time_start) AND YEAR(date_time_end)')->get();
			// echo $leaveday;
			// dd($leaveday);
			if(!is_null($leaveday)) {
				$leavday = [];
				foreach ($leaveday as $key) {
					$period1 = \Carbon\CarbonPeriod::create($key->date_time_start, '1 days', $key->date_time_end);
					foreach ($period1 as $key1) {
						$leavday[] = $key1->format('Y-m-d');
					}
				}
			} else {
				$leavday = [];
			}
		} else {
			$leavday = [];
		}

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

		$unavailableleave = Arr::collapse([$holiday, $sundays, $leavday, $saturdays, $lusa]);
		return response()->json($unavailableleave);
		// print_r($date[1]);
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
}