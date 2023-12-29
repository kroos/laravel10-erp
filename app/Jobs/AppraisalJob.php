<?php

namespace App\Jobs;

// load batch and queue
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

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
use App\Models\HumanResources\HRDisciplinary;
use App\Models\HumanResources\OptBranch;
use App\Models\HumanResources\OptDepartment;

// load helper
use App\Helpers\UnavailableDateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load lib
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Log;
use Exception;

// load laravel-excel
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\StaffAppraisalExport;

class AppraisalJob implements ShouldQueue
{
	use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $staffs;
	protected $year;

	/**
	 * Create a new job instance.
	 */
	public function __construct($staffs, $year)
	{
		$this->staffs = $staffs;
		$this->year = $year;
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$staffs = $this->staffs;
		$year = $this->year;

		$handle = fopen(storage_path('app/public/excel/export.csv'), 'a+') or die();

		$header[0] = [
						// '#',
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
						'Late Frequency (0.5m per time)',
						'UPL Frequency (1day-5day=1m, 6day-10day=2m, >11day=3m)',
						'MC Frequency (9day-10day=1m, 11day-14day=2m, >15=3m)',
						'EL w/o Supporting Doc (0.5m per time)',
						'Absent w/o Notice or didn\'t Refill Form (1m per day)',
						'Absent As Reject By HR (1m per day)',
						'Apply Leave 3 Days Not In Advance (0.5m per time)',
						'UPL (Quarantine)',
						'Verbal Warning (1m per time)',
						'Warning Letter Frequency (3-5m per time)'
					];

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
			$leave[$i] = HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); });
			$attendance = HRAttendance::where('staff_id', $v->id)->whereYear('attend_date', $year)->where('exception', 0);


			// NRL
			$nrlbalance = 0;
			foreach ($nrl as $val) {
				$nrlbalance += $val->leave_balance;
			}

			// UPL/EL-UPL
			$utilizeupl = 0;
			$upl = HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); })->whereIn('leave_type_id', [3, 6])->get();
			foreach ($upl as $val) {
				$utilizeupl += $val->period_day;
			}

			// MC-UPL
			$utilizemcupl = 0;
			$mcupl = HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); })->where('leave_type_id', 11)->get();
			foreach ($mcupl as $val) {
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

			// Freq Late
			$freqs = HRAttendance::where('staff_id', $v->id)
										->whereYear('attend_date', $year)
										->where('exception', 0)
										->whereNull('leave_id')
										->whereNull('outstation_id')
										->where('daytype_id', 1)
										// ->where('in', '!=', '00:00:00')
										// ->where('resume', '!=', '00:00:00')
										// ->get();
										// ->ddrawsql();
										;
			// Late Frequency
			$freqlate = 0;
			foreach ($freqs->where('in', '!=', '00:00:00')->get() as $val) {
				$wh = UnavailableDateTime::workinghourtime($val->attend_date, $v->id)->first();
				if (Carbon::parse($val->in)->gt($wh->time_start_am)) {
					$freqlate++;
				}
			}
			foreach ($freqs->where('resume', '!=', '00:00:00')->get() as $val) {
				$wh = UnavailableDateTime::workinghourtime($val->attend_date, $v->id)->first();
				if (Carbon::parse($val->resume)->gt($wh->time_start_pm)) {
					$freqlate++;
				}
			}

			// UPL Frequency
			$uplfrequency = $upl->count();

			// MC Frequency
			$mcfrequency = 0;
			$mcfrequency += $mcupl->count();
			$mcfrequency += HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); })->where('leave_type_id', 2)->count();

			// EL w/o Supporting Doc
			$elwosupportingdoc = HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); })->whereIn('leave_type_id', [5, 6])->where(function(Builder $query){ $query->whereNull('softcopy')->WhereNull('hardcopy'); })->get()->count();


			// Absent w/o Notice or didn\'t Refill Form
			$absentwonotice = HRAttendance::where('staff_id', $v->id)->whereYear('attend_date', $year)->where('exception', 0)->whereIn('attendance_type_id', [1, 2])->where(function (Builder $query) { $query->whereNull('remarks')->orWhereNull('hr_remarks'); })->get()->count();

			// Absent As Reject By HR (1m per day)
			$rejects = HRLeave::where('staff_id', $v->id)->where('leave_status_id', 4)->get();
			$absentasreject = 0;
			foreach ($rejects as $reject) {
				$days = Carbon::parse($reject->date_time_start)->toPeriod($reject->date_time_end);
				// dd($days);
				// $absentasreject = 0;
				foreach ($days as $day) {
					// check to see if absent
					$absentasreject += HRAttendance::where('attend_date', $day)->where('staff_id', $v->id)->where(function (Builder $query) { $query->where('in', '00:00:00')->where('break', '00:00:00')->where('resume', '00:00:00')->where('out', '00:00:00'); })->where('exception', 0)->get()->count();
					// dd($absentasreject);
				}
			}

			// Apply Leave 3 Days Not In Advance
			$notapplyleave3 = HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); })->whereIn('leave_type_id', [5, 6])->get()->count();

			// UPL (Quarantine)
			$supl = HRLeave::where('staff_id', $v->id)->where(function(Builder $query){ $query->where('leave_status_id', 5)->orWhereNull('leave_status_id'); })->where('leave_type_id', 12)->get()->count();

			// Verbal Warning (1m per time)
			$verbalwarning = HRDisciplinary::where('staff_id', $v->id)->where('disciplinary_action_id', 2)->get()->count();

			// Warning Letter Frequency (3-5m per time)
			$warningletterfrequency = HRDisciplinary::where('staff_id', $v->id)->where('disciplinary_action_id', 3)->get()->count();

			$records[$i] = [/*$i, */$username, $name, $location, $department, /*$age,*/ $datejoined, $dateconfirmed, $altotal, $alutilize, $albalance, $mctotal, $mcutilize, $mcbalance, $nrlbalance, $utilizeupl, $utilizemcupl, $absent, $apparaisalmark1, $apparaisalmark2, $apparaisalmark3, $apparaisalmark4, $apparaisalaveragemark, $freqlate, $uplfrequency, $mcfrequency, $elwosupportingdoc, $absentwonotice, $absentasreject, $notapplyleave3, $supl, $verbalwarning, $warningletterfrequency];
			$i++;
		}
		// $combine = $header + $records;
		// $dataappraisal = collect($combine);
		// Excel::store(new StaffAppraisalExport($dataappraisal), 'Staff_Appraisal_'.$year.'.xlsx');
		foreach ($records as $value) {
			fputcsv($handle, $value);
		}
		fclose($handle);
	}
}
