<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load model
use App\Models\Setting;

use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRLeaveApprovalDirector;
use App\Models\HumanResources\HRLeaveApprovalHOD;
use App\Models\HumanResources\HRLeaveApprovalHR;


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

// load helper
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
					'remarks' => 'Cancelled by '.\Auth::user()->belongstostaff->name,
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

				$addl = $n->period_day + $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_leave_balance;
				$addu = $n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->first()->maternity_leave_utilize - $n->period_day;

				// echo $addl.' masukkan dalam annual balance<br />';

				// find all approval
				// echo $n->hasmanystaffapproval()->get().'find all approval<br />';

				// echo \Auth::user()->belongstostaff->belongtomanyposition()->wherePivot('main', 1)->first()->position.' position <br />';
				// echo \Auth::user()->belongstostaff->name.' position <br />';

				// update the al balance
				$n->belongstostaff->hasmanyleavematernity()->where('year', $dts->format('Y'))->update([
					'maternity_leave_balance' => $addl,
					'maternity_leave_utilize' => $addu,
					'remarks' => 'Cancelled By '.\Auth::user()->belongstostaff->name,
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
				$n->hasmanyleaveapprovalbackup()->update([
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

	public function supervisorstatus(Request $request)
	{
		// return $request->all();
		// exit;
		$validated = $request->validate([
				'leave_status_id' => 'required',
				'verify_code' => 'required_if:leave_status_id,5|numeric|nullable',		// required if only leave_status_id is 5 (Approved)
			],
			[
				'leave_status_id.required' => 'Please choose your approval',
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
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
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
				'leave_status_id.required' => 'Please choose your approval',
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
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
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
				'leave_status_id.required' => 'Please choose your approval',
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
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
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
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
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
				'leave_status_id.required' => 'Please choose your approval',
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
				$sala->update(['mc_leave_balance' => $albal, 'mc_leave_utilize' => $aluti]);
				$sal->update(['period_day' => 0, 'leave_status_id' => $request->leave_status_id]);
			} elseif($saly == 7) {
				$pd = $sal->period_day;											// get period day
				$sala = $sal->belongstomanyleavematernity->first();				// get maternity leave
				$albal = $sala->maternity_leave_balance + $pd;					// maternity leave balance
				$aluti = $sala->maternity_leave_utilize - $pd;					// maternity leave utilize
				$sala->update(['maternity_leave_balance' => $albal, 'maternity_leave_utilize' => $aluti]);
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
