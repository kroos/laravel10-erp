<?php

namespace App\Exports;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\HRLeaveReplacement;
use App\Models\HumanResources\OptBranch;
use App\Models\HumanResources\OptDepartment;

use Illuminate\Http\Request;

// load helper
use App\Helpers\TimeCalculator;
use App\Helpers\UnavailableDateTime;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Log;
use Exception;

use Maatwebsite\Excel\Concerns\FromCollection;

class StaffAppraisalExport implements FromCollection
{
	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	/**
	* @return \Illuminate\Support\Collection
	*/
	public function collection()
	{
		$year = $this->request;

		$header[0] = [
						'#',
						'Emp. No',
						'Staff Name',
						'Location',
						'Department',
						// 'Age',
						'Date Joined',
						'Date Confirmed',
						'Annual Leave Entitlement',
						'Utilize Annual Leave',
						'Balance Annual Leave',
						'MC Entitlement',
						'Utilize MC',
						'Balance MC',
						'Balance NRL',
						'Utilize UPL',
						'Utilize MC-UPL',
						'Absent',
						'Apparaisal Mark1',
						'Apparaisal Mark2',
						'Apparaisal Mark3',
						'Apparaisal Mark4',
						'Apparaisal Average Mark',
						// 'Freq Late (0.5m per time)',
						// 'Freq UPL (1day-5day=1m, 6day-10day=2m, >11day=3m)',
						// 'Freq MC (9day-10day=1m, 11day-14day=2m, >15=3m)',
						// 'EL w/o Supporting Doc (0.5m per time)',
						// 'Absent w/o notice or didn\'t refill form (1m per day)',
						// 'Absent As Reject By HR (1m per day)',
						// 'No Apply Leave 3 Days In Advance (0.5m per time)',
						// 'UPL (Quarantine)',
						// 'Verbal Warning (1m per time)',
						// 'Warning 1, 2 & 3 (3-5m per time'
					];

		$staffs = Staff::where('active', 1)->get();
		// $logins = Login::()

		$i = 1;
		foreach ($staffs as $v) {
			$username = $v->hasmanylogin()->where('active', 1)->first()->username;
			$name = $v->name;
			$pivot = $v->belongstomanydepartment()->wherePivot('main', 1)->first();
			$location = OptBranch::find($pivot->branch_id)->location;
			$department = OptDepartment::find($pivot->department_id)->department;
			$datejoined = ($v->join)?Carbon::parse($v->join)->format('j M Y'):NULL;
			$dateconfirmed = ($v->confirmed)?Carbon::parse($v->confirmed)->format('j M Y'):NULL;
			$ALentitlements = HRLeaveAnnual::where([['staff_id', $v->id], ['year', $year]])->first();
			$altotal = $ALentitlements?->annual_leave;
			$alutilize = $ALentitlements?->annual_leave_utilize;
			$albalance = $ALentitlements?->annual_leave_balance;
			$MCentitlements = HRLeaveMC::where([['staff_id', $v->id], ['year', $year]])->first();
			$mctotal = $MCentitlements?->mc_leave;
			$mcutilize = $MCentitlements?->mc_leave_utilize;
			$mcbalance = $MCentitlements?->mc_leave_balance;
			$nrl = HRLeaveReplacement::where([['staff_id', $v->id], ['leave_balance', '!=', 0]])->get();
			$leave = HRLeave::where('staff_id', $v->id)->where('leave_status_id', 5);
			$attendance = HRAttendance::where('staff_id', $v->id)->whereYear('attend_date', $year)->where('exception', 0);


			// NRL
			$nrlbalance = 0;
			foreach ($nrl as $val) {
				$nrlbalance += $val->leave_balance;
			}

			// UPL/EL-UPL
			$utilizeupl = 0;
			foreach ($leave?->whereIn('leave_type_id', [3, 6])->get() as $val) {
				$utilizeupl += $val->period_day;
			}

			// MC-UPL
			$utilizemcupl = 0;
			foreach ($leave?->where('leave_type_id', 11)->get() as $val) {
				$utilizemcupl += $val->period_day;
			}

			// absent
			$absent = 0;
			// $attendance?->whereIn('attendance_type_id', [1, 2])->ddrawsql();
			foreach ($attendance?->whereIn('attendance_type_id', [1, 2])->get() as $val) {
				if ($val->attendance_type_id == 1) {
					$absent += 1;
				} elseif ($val->attendance_type_id == 2) {
					$absent += 0.5;
				}
			}

			// Apparaisal Mark1
			$apparaisalmark1 = NULL;

			// Apparaisal Mark2
			$apparaisalmark2 = NULL;

			// Apparaisal Mark3
			$apparaisalmark3 = NULL;

			// Apparaisal Mark4
			$apparaisalmark4 = NULL;

			// Apparaisal Average Mark
			$apparaisalaveragemark = NULL;













			$records[$i] = [$i, $username, $name, $location, $department, /*$age,*/ $datejoined, $dateconfirmed, $altotal, $alutilize, $albalance, $mctotal, $mcutilize, $mcbalance, $nrlbalance, $utilizeupl, $utilizemcupl, $absent, $apparaisalmark1, $apparaisalmark2, $apparaisalmark3, $apparaisalmark4, $apparaisalaveragemark];
			$i++;
		}
		$combine = $header + $records;
		return collect($combine);
	}
}


