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
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROvertimeRange;

// load helper
use App\Helpers\TimeCalculator;
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

class AttendancePayslipJob implements ShouldQueue
{
	use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $hratt;
	protected $request;

	/**
	 * Create a new job instance.
	 */
	public function __construct($hratt, $request)
	{
		$this->hratt = $hratt;
		$this->request = $request;
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$hratt = $this->hratt;
		$request = $this->request;
		// dd($hratt, $request);

		foreach ($hratt as $k1 => $v1) {
			// dd($v1);
			$login = Login::where([['staff_id', $v1['staff_id']], ['active', 1]])->first()?->username;
			$name = Staff::find($v1['staff_id'])->name;

			// find leave in attendance
			$sattendances = HRAttendance::where(function (Builder $query) use ($request) {
					$query->whereDate('attend_date', '>=', $request['from'])
						->whereDate('attend_date', '<=', $request['to']);
				})
				->where('staff_id', $v1['staff_id'])
				->get();
				// ->dumpRawSql();

			$al = 0;
			$nrl = 0;
			$mc = 0;
			$upl = 0;
			$absent = 0;
			$mcupl = 0;
			$lateness = 0;
			$earlyout = 0;
			$nopayhour = 0;
			$ml = 0;
			$hosp = 0;
			$supl = 0;
			$compasleave = 0;
			$marriageLeave = 0;
			$daywork = 0;
			$ot1 = 0;
			$ot05 = 0;
			$ot[$k1] = [];
			$tf[$k1] = [];



			// loop attendance foreach staff to find leave, absent, OT, lateness and early out
			$i = 1;
			foreach ($sattendances as $sattendance) {
				$wh = UnavailableDateTime::workinghourtime($sattendance->attend_date, $sattendance->staff_id)->first();

				// find leave & leave type
				if (!is_null($sattendance->leave_id)) {
					$leave = HRLeave::find($sattendance->leave_id);
					// dump($leave);
					// dump($leave->leave_id.' => leave_id', $leave->leave_type_id.' => leave_type_id');
					// AL & EL-AL
					if ($leave->leave_type_id == 1 || $leave->leave_type_id == 5) {
						if (!is_null($leave->half_type_id)) {
							$al += 0.5;
						} else {
							$al += 1;
						}
					}
					// MC
					if ($leave->leave_type_id == 2) {
						if (!is_null($leave->half_type_id)) {
							$mc += 0.5;
						} else {
							$mc += 1;
						}
					}
					// ML
					if ($leave->leave_type_id == 7) {
						if (!is_null($leave->half_type_id)) {
							$ml += 0.5;
						} else {
							$ml += 1;
						}
					}
					// NRL & EL-NRL
					if ($leave->leave_type_id == 4 || $leave->leave_type_id == 10) {
						if (!is_null($leave->half_type_id)) {
							$nrl += 0.5;
						} else {
							$nrl += 1;
						}
					}
					// UPL & EL-UPL
					if ($leave->leave_type_id == 3 || $leave->leave_type_id == 6) {
						if (!is_null($leave->half_type_id)) {
							$upl += 0.5;
						} else {
							$upl += 1;
						}
					}
					// MC-UPL
					if ($leave->leave_type_id == 11) {
						if (!is_null($leave->half_type_id)) {
							$mcupl += 0.5;
						} else {
							$mcupl += 1;
						}
					}
					// TF
					if ($leave->leave_type_id == 9) {
						$tf[$k1][$i] = $leave->period_time;
					}
					// S-UPL
					if ($leave->leave_type_id == 12) {
						if (!is_null($leave->half_type_id)) {
							$supl += 0.5;
						} else {
							$supl += 1;
						}
					}
				}

				// loop of each staff with attendance to find absent
				if (!is_null($sattendance->attendance_type_id)) {
					if ($sattendance->attendance_type_id == 1) {
						$absent += 1;
					}
					if ($sattendance->attendance_type_id == 2) {
						$absent += 0.5;
					}
				}

				// loop of each staff with attendance to find overtime
				if (!is_null($sattendance->overtime_id)) {
					$otot = HROvertime::find($sattendance->overtime_id);
					// dd($ot1);
					$ot[$k1][$i] = $otot->belongstoovertimerange->total_time;
				}

				// checking on lateness and early out with no ecxeption when there is no leave, & outstation
				if ( ($sattendance->exception != 1) ) {
					if (is_null($sattendance->outstation_id)) {
						if (is_null($sattendance->leave_id)) {
							// no overtime
							if (is_null($sattendance->overtime_id)) {
								if (($sattendance->in != '00:00:00') && (Carbon::parse($sattendance->in)->gt($wh->time_start_am))) {
									$late = Carbon::parse($wh->time_start_am)->addMinute()->toPeriod($sattendance->in, 1, 'minute');
									// $lateness += $late->count() - 1;
									$lateness += $late->count();
								}
								// early out with no overtime
								if ((Carbon::parse($sattendance->out)->lt($wh->time_end_pm)) && ($sattendance->out != '00:00:00')) {
									$early = Carbon::parse($sattendance->out)->addMinute()->toPeriod($wh->time_end_pm, 1, 'minute');
									// $earlyout += $early->count() - 1;
									$earlyout += $early->count();
								}
							}

							// with overtime
							if(!is_null($sattendance->overtime_id)) {
								// lateness with overtime with exception of morning overtime
								if (($sattendance->in != '00:00:00') && (Carbon::parse($sattendance->in)->gt($wh->time_start_am)) && (HROvertime::find($sattendance->overtime_id)->belongstoovertimerange->id == 26)) {
									$late = Carbon::parse(HROvertime::find($sattendance->overtime_id)->belongstoovertimerange->start)->addMinute()->toPeriod($sattendance->in, 1, 'minute');
									// $lateness += $late->count() - 1;
									$lateness += $late->count();
								} else {
									$late = Carbon::parse($wh->time_start_am)->addMinute()->toPeriod($sattendance->in, 1, 'minute');
									// $lateness += $late->count() - 1;
									$lateness += $late->count();
								}
								// early out with overtime
								// find overtime
								$ota = HROvertime::find($sattendance->overtime_id);
								$endottime = $ota->belongstoovertimerange->end;
								if (($sattendance->out != '00:00:00') && (Carbon::parse($sattendance->out)->lt($endottime)) && ($sattendance->out != '00:00:00')) {
									$early = Carbon::parse($sattendance->out)->addMinute()->toPeriod($endottime, 1, 'minute');
									// $earlyout += $early->count() - 1;
									$earlyout += $early->count();
								}
							}
						}
					}
				}
				$i++;
			}
			// dump($ot[$k1], ' staff_id '.$sattendance->staff_id);

			if (!empty($tf[$k1])) {
				$tf1 = TimeCalculator::total_time($tf[$k1]);
			} else {
				$tf1 = null;
			}

			if (!empty($ot[$k1])) {
				$ot2 = TimeCalculator::total_time($ot[$k1]);
			} else {
				$ot2 = null;
			}
			$records[$k1] = [$login, $name, $al, $nrl, $mc, $upl, $absent, $mcupl, $lateness, $earlyout, $nopayhour, $ml, $hosp, $supl, $compasleave, $marriageLeave, $daywork, $ot1, $ot05, $ot2, $tf1];
		}
		// dd($records);

		$handle = fopen(storage_path('app/public/excel/payslip.csv'), 'a+');
		foreach ($records as $value) {
			fputcsv($handle, $value);
		}
		fclose($handle);
	}
}
