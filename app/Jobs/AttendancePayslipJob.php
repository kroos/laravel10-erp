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
use App\Models\HumanResources\OptCategory;
use App\Models\HumanResources\HRAttendancePayslipSetting;

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
	public function handle()//: void
	{
		$hratt = $this->hratt;
		$request = $this->request;
		// dd($hratt, $request);

		foreach ($hratt as $k1 => $v1) {
			// dd($v1);
			$login[$k1] = Login::where([['staff_id', $v1['staff_id']], ['active', 1]])->first()?->username;
			$name[$k1] = Staff::find($v1['staff_id'])->name;
			// $category = Staff::find($v1['staff_id'])->belongstomanydepartment->belongstocategory->category;
			$cate[$k1] = Staff::find($v1['staff_id'])->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id;
			$category[$k1] = OptCategory::find($cate[$k1])->category;

			// find leave in attendance
			$sattendances[$k1] = HRAttendance::where(function (Builder $query) use ($request) {
					$query->whereDate('attend_date', '>=', $request['from'])
						->whereDate('attend_date', '<=', $request['to']);
				})
				->where('staff_id', $v1['staff_id'])
				->get();
				// ->dumpRawSql();

			$al[$k1] = 0;
			$nrl[$k1] = 0;
			$mc[$k1] = 0;
			$upl[$k1] = 0;
			$absent[$k1] = 0;
			$mcupl[$k1] = 0;
			$lateness[$k1] = 0;
			$earlyout[$k1] = 0;
			$nopayhour[$k1] = 0;
			$ml[$k1] = 0;
			$hosp[$k1] = 0;
			$supl[$k1] = 0;
			$compasleave[$k1] = 0;
			$marriageLeave[$k1] = 0;
			$daywork[$k1] = 0;
			$ot1[$k1] = 0;
			$ot05[$k1] = 0;
			$ot[$k1] = [];
			$tf[$k1] = [];
			$latemerit[$k1] = 0;
			$earlymerit[$k1] = 0;

			// loop attendance foreach staff to find leave, absent, OT, lateness and early out
			foreach ($sattendances[$k1] as $k2 => $sattendance) {
				$wh[$k1][$k2] = UnavailableDateTime::workinghourtime($sattendance->attend_date, $sattendance->staff_id)->first();

				$leave[$k1][$k2] = HRLeave::where(function(Builder $query) use ($sattendance) {
														$query->whereDate('date_time_start' , '<=', $sattendance->attend_date)
															->whereDate('date_time_end' , '>=', $sattendance->attend_date);
													})
													->where('staff_id', $sattendance->staff_id)
													->where(function(Builder $query) {
														$query->whereNull('leave_status_id')
															->orWhereIn('leave_status_id', [5, 6]);
													})
													->get();
													// ->dumpRawSql();

				// find leave & leave type
				// need to make sure if there are 2 leaves in 1 day
				if (!is_null($sattendance->leave_id)) {
					foreach ($leave[$k1][$k2] as $k3 => $v3) {

						// AL & EL-AL
						if ($v3->leave_type_id == 1 || $v3->leave_type_id == 5) {
							if (!is_null($v3->half_type_id)) {
								$al[$k1] += 0.5;
							} else {
								$al[$k1] += 1;
							}
						}
						// MC
						if ($v3->leave_type_id == 2) {
							if (!is_null($v3->half_type_id)) {
								$mc[$k1] += 0.5;
							} else {
								$mc[$k1] += 1;
							}
						}
						// ML
						if ($v3->leave_type_id == 7) {
							if (!is_null($v3->half_type_id)) {
								$ml[$k1] += 0.5;
							} else {
								$ml[$k1] += 1;
							}
						}
						// NRL & EL-NRL
						if ($v3->leave_type_id == 4 || $v3->leave_type_id == 10) {
							if (!is_null($v3->half_type_id)) {
								$nrl[$k1] += 0.5;
							} else {
								$nrl[$k1] += 1;
							}
						}
						// UPL & EL-UPL
						if ($v3->leave_type_id == 3 || $v3->leave_type_id == 6) {
							if (!is_null($v3->half_type_id)) {
								$upl[$k1] += 0.5;
							} else {
								$upl[$k1] += 1;
							}
						}
						// MC-UPL
						if ($v3->leave_type_id == 11) {
							if (!is_null($v3->half_type_id)) {
								$mcupl[$k1] += 0.5;
							} else {
								$mcupl[$k1] += 1;
							}
						}
						// TF
						if ($v3->leave_type_id == 9) {
							$tf[$k1][$k2] = $v3->period_time;
						}
						// S-UPL
						if ($v3->leave_type_id == 12) {
							if (!is_null($v3->half_type_id)) {
								$supl[$k1] += 0.5;
							} else {
								$supl[$k1] += 1;
							}
						}
					}
				}

				// loop of each staff with attendance to find absent
				if (!is_null($sattendance->attendance_type_id)) {
					if ($sattendance->attendance_type_id == 1) {
						$absent[$k1] += 1;
					}
					if ($sattendance->attendance_type_id == 2) {
						$absent[$k1] += 0.5;
					}
				}

				// loop of each staff with attendance to find overtime
				if (!is_null($sattendance->overtime_id)) {
					$otot[$k1][$k2] = HROvertime::find($sattendance->overtime_id);
					$ot[$k1][$k2] = $otot[$k1][$k2]->belongstoovertimerange->total_time;
				}

				// checking on lateness and early out with no ecxeption when there is no leave, & outstation
				if ( ($sattendance->exception != 1) ) {
					if (is_null($sattendance->outstation_id)) {
						// no leave
						if (is_null($sattendance->leave_id)) {
							// no overtime
							if (is_null($sattendance->overtime_id)) {

								// no exception | no outstation | no leave | no overtime
								// morning
								if (($sattendance->in != '00:00:00') && (Carbon::parse($sattendance->in)->gt($wh[$k1][$k2]->time_start_am))) {
									$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_am)->addMinute()->toPeriod($sattendance->in, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
									// $lateness += $late->count() - 1;
									// $lateness[$k1] += $late[$k1][$k2]->count();

									if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$lateness[$k1] += 15;
									}

									if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$lateness[$k1] += 30;
									}

									if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$lateness[$k1] += 45;
									}

									if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$lateness[$k1] += 60;
									}

									if ($late[$k1][$k2]->count() > 60) {
										// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$lateness[$k1] += 60;
									}
								}

								if (($sattendance->resume != '00:00:00') && (Carbon::parse($sattendance->resume)->gt($wh[$k1][$k2]->time_start_pm))) {
									$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_pm)->addMinute()->toPeriod($sattendance->resume, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
									// $lateness += $late->count() - 1;
									// $lateness[$k1] += $late[$k1][$k2]->count();

									if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$lateness[$k1] += 15;
									}

									if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$lateness[$k1] += 30;
									}

									if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$lateness[$k1] += 45;
									}

									if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$lateness[$k1] += 60;
									}

									if ($late[$k1][$k2]->count() > 60) {
										// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$lateness[$k1] += 60;
									}
								}

								// early break with no overtime
								if ((Carbon::parse($sattendance->break)->lt($wh[$k1][$k2]->time_end_am)) && ($sattendance->break != '00:00:00')) {
									$early[$k1][$k2] = Carbon::parse($sattendance->break)->addMinute()->toPeriod($wh[$k1][$k2]->time_end_am, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
									// $earlyout += $early->count() - 1;
									// $earlyout[$k1] += $early[$k1][$k2]->count();

									if ($early[$k1][$k2]->count() > 0 && $early[$k1][$k2]->count() <= 15) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$earlyout[$k1] += 15;
									}

									if ($early[$k1][$k2]->count() > 15 && $early[$k1][$k2]->count() <= 30) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$earlyout[$k1] += 30;
									}

									if ($early[$k1][$k2]->count() > 30 && $early[$k1][$k2]->count() <= 45) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$earlyout[$k1] += 45;
									}

									if ($early[$k1][$k2]->count() > 45 && $early[$k1][$k2]->count() <= 60) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$earlyout[$k1] += 60;
									}

									if ($early[$k1][$k2]->count() > 60) {
										// $earlymerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($earlyout[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$earlymerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($early[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$earlyout[$k1] += 60;
									}
								}

								// early out with no overtime
								if ((Carbon::parse($sattendance->out)->lt($wh[$k1][$k2]->time_end_pm)) && ($sattendance->out != '00:00:00')) {
									$early[$k1][$k2] = Carbon::parse($sattendance->out)->addMinute()->toPeriod($wh[$k1][$k2]->time_end_pm, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);

									// $earlyout[$k1] += $early[$k1][$k2]->count();

									if ($early[$k1][$k2]->count() > 0 && $early[$k1][$k2]->count() <= 15) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$earlyout[$k1] += 15;
									}

									if ($early[$k1][$k2]->count() > 15 && $early[$k1][$k2]->count() <= 30) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$earlyout[$k1] += 30;
									}

									if ($early[$k1][$k2]->count() > 30 && $early[$k1][$k2]->count() <= 45) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$earlyout[$k1] += 45;
									}

									if ($early[$k1][$k2]->count() > 45 && $early[$k1][$k2]->count() <= 60) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$earlyout[$k1] += 60;
									}

									if ($early[$k1][$k2]->count() > 60) {
										// $earlymerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($earlyout[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$earlymerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($early[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$earlyout[$k1] += 60;
									}
								}



							} else {
								// no exception | no outstation | no leave | overtime
								// lateness with overtime with exception of morning overtime
								if (($sattendance->in != '00:00:00') && (Carbon::parse($sattendance->in)->gt($wh[$k1][$k2]->time_start_am)) && (HROvertime::find($sattendance->overtime_id)->belongstoovertimerange->id == 26)) {
									$late[$k1][$k2] = Carbon::parse(HROvertime::find($sattendance->overtime_id)->belongstoovertimerange->start)->addMinute()->toPeriod($sattendance->in, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
									// $lateness += $late->count() - 1;
									// $lateness[$k1] += $late[$k1][$k2]->count();
									if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$lateness[$k1] += 15;
									}

									if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$lateness[$k1] += 30;
									}

									if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$lateness[$k1] += 45;
									}

									if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$lateness[$k1] += 60;
									}

									if ($late[$k1][$k2]->count() > 60) {
										// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$lateness[$k1] += 60;
									}
								}

								if (($sattendance->resume != '00:00:00') && (Carbon::parse($sattendance->resume)->gt($wh[$k1][$k2]->time_start_pm))) {
									$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_pm)->addMinute()->toPeriod($sattendance->resume, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
									// $lateness += $late->count() - 1;
									// $lateness[$k1] += $late[$k1][$k2]->count();
									if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$lateness[$k1] += 15;
									}

									if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$lateness[$k1] += 30;
									}

									if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$lateness[$k1] += 45;
									}

									if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
										$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$lateness[$k1] += 60;
									}

									if ($late[$k1][$k2]->count() > 60) {
										// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$lateness[$k1] += 60;
									}
								}

								// early out with overtime
								// find overtime
								$ota[$k1][$k2] = HROvertime::find($sattendance->overtime_id);
								$endottime[$k1][$k2] = $ota[$k1][$k2]->belongstoovertimerange->end;
								if (($sattendance->out != '00:00:00') && (Carbon::parse($sattendance->out)->lt($endottime[$k1][$k2])) && ($sattendance->out != '00:00:00')) {
									$early[$k1][$k2] = Carbon::parse($sattendance->out)->addMinute()->toPeriod($endottime[$k1][$k2], 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
									// $earlyout += $early->count() - 1;
									// $earlyout[$k1][$k2] += $early[$k1][$k2]->count();

									if ($early[$k1][$k2]->count() > 0 && $early[$k1][$k2]->count() <= 15) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
										$earlyout[$k1][$k2] += 15;
									}

									if ($early[$k1][$k2]->count() > 15 && $early[$k1][$k2]->count() <= 30) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
										$earlyout[$k1][$k2] += 30;
									}

									if ($early[$k1][$k2]->count() > 30 && $early[$k1][$k2]->count() <= 45) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
										$earlyout[$k1][$k2] += 45;
									}

									if ($early[$k1][$k2]->count() > 45 && $early[$k1][$k2]->count() <= 60) {
										$earlymerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
										$earlyout[$k1][$k2] += 60;
									}

									if ($early[$k1][$k2]->count() > 60) {
										// $earlymerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($earlyout[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
										$earlymerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($early[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
										$earlyout[$k1][$k2] += 60;
									}
								}
							}

						} else {
							// no exception | no outstation | leave | no overtime
							// check late when leave on half day leave
							// if leave am, check on late resume, if leave pm, check on late in
							$leaveHalf = HRLeave::find($sattendance->leave_id);
							if ($leaveHalf?->leave_type_id != 9) {
								if ($leaveHalf?->half_type_id == 1) {
									// morning leave, need to check on late resume
									if (($sattendance->resume != '00:00:00') && (Carbon::parse($sattendance->resume)->gt($wh[$k1][$k2]->time_start_pm))) {
										$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_pm)->addMinute()->toPeriod($sattendance->resume, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
										// $lateness += $late->count() - 1;
										// $lateness[$k1] += $late[$k1][$k2]->count();

										if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
											$lateness[$k1] += 15;
										}

										if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
											$lateness[$k1] += 30;
										}

										if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
											$lateness[$k1] += 45;
										}

										if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
											$lateness[$k1] += 60;
										}

										if ($late[$k1][$k2]->count() > 60) {
											// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
											$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
											$lateness[$k1] += 60;
										}
									}
								} else {
									// evening leave, need to check on late in
									if (($sattendance->in != '00:00:00') && (Carbon::parse($sattendance->in)->gt($wh[$k1][$k2]->time_start_am))) {
										$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_am)->addMinute()->toPeriod($sattendance->in, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
										// $lateness += $late->count() - 1;
										// $lateness[$k1] += $late[$k1][$k2]->count();

										if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
											$lateness[$k1] += 15;
										}

										if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
											$lateness[$k1] += 30;
										}

										if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
											$lateness[$k1] += 45;
										}

										if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
											$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
											$lateness[$k1] += 60;
										}

										if ($late[$k1][$k2]->count() > 60) {
											// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
											$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
											$lateness[$k1] += 60;
										}
									}

								}
							}
						}
					} else {
						// outstation nearby
						if (($sattendance->in != '00:00:00') && (Carbon::parse($sattendance->in)->gt($wh[$k1][$k2]->time_start_am))) {
							$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_am)->addMinute()->toPeriod($sattendance->in, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
							// $lateness += $late->count() - 1;
							// $lateness[$k1] += $late[$k1][$k2]->count();

							if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
								$lateness[$k1] += 15;
							}

							if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
								$lateness[$k1] += 30;
							}

							if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
								$lateness[$k1] += 45;
							}

							if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
								$lateness[$k1] += 60;
							}

							if ($late[$k1][$k2]->count() > 60) {
								// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
								$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
								$lateness[$k1] += 60;
							}
						}

						if (($sattendance->resume != '00:00:00') && (Carbon::parse($sattendance->resume)->gt($wh[$k1][$k2]->time_start_pm))) {
							$late[$k1][$k2] = Carbon::parse($wh[$k1][$k2]->time_start_pm)->addMinute()->toPeriod($sattendance->resume, 1, 'minute', CarbonPeriod::EXCLUDE_START_DATE);
							// $lateness += $late->count() - 1;
							// $lateness[$k1] += $late[$k1][$k2]->count();

							if ($late[$k1][$k2]->count() > 0 && $late[$k1][$k2]->count() <= 15) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(1)->value;
								$lateness[$k1] += 15;
							}

							if ($late[$k1][$k2]->count() > 15 && $late[$k1][$k2]->count() <= 30) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(2)->value;
								$lateness[$k1] += 30;
							}

							if ($late[$k1][$k2]->count() > 30 && $late[$k1][$k2]->count() <= 45) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(3)->value;
								$lateness[$k1] += 45;
							}

							if ($late[$k1][$k2]->count() > 45 && $late[$k1][$k2]->count() <= 60) {
								$latemerit[$k1] += HRAttendancePayslipSetting::find(4)->value;
								$lateness[$k1] += 60;
							}

							if ($late[$k1][$k2]->count() > 60) {
								// $latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($lateness[$k1] - 61) * HRAttendancePayslipSetting::find(5)->value);
								$latemerit[$k1] += (HRAttendancePayslipSetting::find(4)->value) + (($late[$k1][$k2]->count() - 61) * HRAttendancePayslipSetting::find(5)->value);
								$lateness[$k1] += 60;
							}
						}

					}
				}
			}
			// dump($ot[$k1], ' staff_id '.$sattendance->staff_id);

			if (!empty($tf[$k1])) {
				$tf1a[$k1] = TimeCalculator::total_time($tf[$k1]);
				$tfa[$k1] = explode(':', $tf1a[$k1]);
				$nft[$k1] = number_format(($tfa[$k1][1] / 60), 2);
				$tf1[$k1] = ($tfa[$k1][0] + $nft[$k1]).' hours - '.$tf1a[$k1];
			} else {
				$tf1[$k1] = null;
			}

			if (!empty($ot[$k1])) {
				$ot2a[$k1] = TimeCalculator::total_time($ot[$k1]);
				$ota[$k1] = explode(':', $ot2a[$k1]);
				$nfo[$k1] = number_format(($ota[$k1][1] / 60), 2);
				$ot2[$k1] = ($ota[$k1][0] + $nfo[$k1]).' hours - '.$ot2a[$k1];
			} else {
				$ot2[$k1] = null;
			}


			// nullified if zero
			if ($al[$k1] < 0.5) {
				$al[$k1] = null;
			}

			if ($mc[$k1] < 0.5) {
				$mc[$k1] = null;
			}

			if ($ml[$k1] < 0.5) {
				$ml[$k1] = null;
			}

			if ($nrl[$k1] < 0.5) {
				$nrl[$k1] = null;
			}

			if ($upl[$k1] < 0.5) {
				$upl[$k1] = null;
			}

			if ($mcupl[$k1] < 0.5) {
				$mcupl[$k1] = null;
			}

			if ($supl[$k1] < 0.5) {
				$supl[$k1] = null;
			}

			if ($absent[$k1] < 0.5) {
				$absent[$k1] = null;
			}

			if ($lateness[$k1] < 1) {
				$laten[$k1] = null;
			} else {
				$laten[$k1] = $latemerit[$k1].' ('.$lateness[$k1].' minutes)';
			}

			if ($earlyout[$k1] < 1) {
				$earl[$k1] = null;
			} else {
				$earl[$k1] = $earlymerit[$k1].' ('.$earlyout[$k1].' minutes)';
			}

			if ($nopayhour[$k1] < 0.5) {
				$nopayhour[$k1] = null;
			}

			if ($hosp[$k1] < 0.5) {
				$hosp[$k1] = null;
			}

			if ($compasleave[$k1] < 0.5) {
				$compasleave[$k1] = null;
			}

			if ($marriageLeave[$k1] < 0.5) {
				$marriageLeave[$k1] = null;
			}

			if ($daywork[$k1] < 0.5) {
				$daywork[$k1] = null;
			}

			if ($ot1[$k1] < 0.5) {
				$ot1[$k1] = null;
			}

			if ($ot05[$k1] < 0.5) {
				$ot05[$k1] = null;
			}

			$records[$k1] = [$login[$k1], $name[$k1], $category[$k1], $al[$k1], $nrl[$k1], $mc[$k1], $upl[$k1], $absent[$k1], $mcupl[$k1], $laten[$k1], $earl[$k1], $nopayhour[$k1], $ml[$k1], $hosp[$k1], $supl[$k1], $compasleave[$k1], $marriageLeave[$k1], $daywork[$k1], $ot1[$k1], $ot05[$k1], $ot2[$k1], $tf1[$k1]];
		}
		// dump($records);

		$handle = fopen(storage_path('app/public/excel/payslip.csv'), 'a+');
		foreach ($records as $value) {
			fputcsv($handle, $value);
		}
		fclose($handle);
	}

}
