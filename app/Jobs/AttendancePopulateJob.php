<?php
namespace App\Jobs;

// load model
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRTempPunchTime;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRRestdayCalendar;
use App\Models\HumanResources\OptWorkingHour;

// load db facade
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

// load batch and queue
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

// load helper
use App\Helpers\UnavailableDateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load lib
use \Carbon\Carbon;

class AttendancePopulateJob implements ShouldQueue
{
	use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $emps;
	protected $datelast;
	protected $datecurrent;

	/**
	 * Create a new job instance.
	 */
	public function __construct($emps, $datelast, $datecurrent)
	{
		$this->emps = $emps;
		$this->datelast = $datelast;
		$this->datecurrent = $datecurrent;
	}

	/**
	 * Execute the job.
	 */
	public function handle()/*: void*/
	{
		// $emps = $this->emps;
		// $datelast = $this->datelast;
		// $datecurrent = $this->datecurrent;
		// dd($emps, $row_Recordset3, $row_Recordset5);

		// LOOP STAFF FROM ARRAY TO FETCH THE ATTENDANCE
		foreach ($this->emps as $employee) {
			for ($a = strtotime($this->datelast); $a <= strtotime($this->datecurrent); $a = strtotime('+1 day', $a)) {
				$date = date('Y-m-d', $a);
				$date_name = date('l', $a);

				$totalRows_holiday = HRHolidayCalendar::select('holiday')->where('date_start', '<=', $date)->where('date_end', '>=', $date)->count();

				$totalRows_saturday = HRRestdayCalendar::select('saturday_date')->where('restday_group_id', '=', $employee['restday_group_id'])->where('saturday_date', '=', $date)->count();

				if ($totalRows_holiday > 0) {
				// HOLIDAY
					$daytype_id = '3';
				} elseif ($totalRows_saturday > 0 || $date_name == 'Sunday') {
				// RESTDAY
					$daytype_id = '2';
				} else {
				// WORKDAY
					$daytype_id = '1';
				}


				// ----------------------------------------------------------- CONFIGURE IN BREAK RESUME OUT TIME -----------------------------------------------------------//
				if ($date_name == 'Friday' && $employee['wh_group_id'] == '0') {
					$row_work_hour = OptWorkingHour::select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')->where('effective_date_start', '<=', $date)->where('effective_date_end', '>=', $date)->where('group', '=', $employee['wh_group_id'])->where('category', '=', 3)->first();
				} elseif ($employee['wh_group_id'] == '0') {
					$row_work_hour = OptWorkingHour::select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')->where('effective_date_start', '<=', $date)->where('effective_date_end', '>=', $date)->where('group', '=', $employee['wh_group_id'])->where('category', '!=', 3)->first();
				} else {
					$row_work_hour = OptWorkingHour::select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')->where('effective_date_start', '<=', $date)->where('effective_date_end', '>=', $date)->where('group', '=', $employee['wh_group_id'])->where('category', '=', 8)->first();
				}


				// IN
				$in = "00:00:00";
				$row_IN = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $employee['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(Att_Time) 	<= ?', [$row_work_hour->time_end_am])->groupBy('formatted_time')->orderBy('Att_Time', 'asc')->first();
				if ($row_IN != NULL) {
					$in = $row_IN->formatted_time;

					$in_add = Carbon::parse($in)->addMinutes(1)->format('H:i:s');;
				}


				// OUT
				$out = "00:00:00";
				$row_OUT = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $employee['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(	Att_Time) >= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'desc')->first();
				if ($row_OUT != NULL) {
					$out = $row_OUT->formatted_time;
				}


				// BREAK1 (FETCH THE LAST ROW BEFORE BREAK TIME)
				$break1 = "00:00:00";
				$row_BREAK1 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $employee['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(	Att_Time) <= ?', [$row_work_hour->time_end_am])->groupBy('formatted_time')->orderBy('Att_Time', 'desc')->first();
				if ($row_BREAK1 != NULL) {
					$break1 = $row_BREAK1->formatted_time;
				}


				// BREAK2 (FETCH THE FIRST ROW BETWEEN BREAK AND RESUME)
				$break2 = "00:00:00";
				$break2_difference = "00:00";
				$row_BREAK2 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $employee['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(	Att_Time) >= ?', [$row_work_hour->time_end_am])->whereRaw('TIME(Att_Time) <= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'asc')->first();
				if ($row_BREAK2 != NULL) {
					$break2 = $row_BREAK2->formatted_time;

					// CALCULATE INTERVAL TIME BETWEEN PUNCH AND BREAK
					$break2_date1 = Carbon::parse($date . ' ' . $row_work_hour->time_end_am);
					$break2_date2 = Carbon::parse($date . ' ' . $row_BREAK2->formatted_time);
					$break2_interval = $break2_date1->diff($break2_date2);
					$break2_difference = ($break2_interval->h * 60) + $break2_interval->i;
				}


				// RESUME1 (FETCH THE FIRST ROW AFTER RESUME TIME)
				$resume1 = "00:00:00";
				$row_RESUME1 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $employee['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(	Att_Time) >= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'asc')->first();
				if ($row_RESUME1 != NULL) {
					$resume1 = $row_RESUME1->formatted_time;
				}


				// RESUME2 (FETCH THE LAST ROW BETWEEN BREAK AND RESUME)
				$resume2 = "00:00:00";
				$resume2_difference = "00:00";
				$row_RESUME2 = HRTempPunchTime::selectRAW("DATE_FORMAT(Att_Time, '%H:%i:00') AS formatted_time")->where('EmployeeCode', '=', $employee['username'])->whereRaw('DATE(Att_Time) = ?', [$date])->whereRaw('TIME(	Att_Time) >= ?', [$row_work_hour->time_end_am])->whereRaw('TIME(Att_Time) <= ?', [$row_work_hour->time_start_pm])->groupBy('formatted_time')->orderBy('Att_Time', 'desc')->first();
				if ($row_RESUME2 != NULL) {
					$resume2 = $row_RESUME2['formatted_time'];

					// CALCULATE INTERVAL TIME BETWEEN PUNCH AND RESUME
					$resume2_date1 = Carbon::parse($date . ' ' . $row_work_hour->time_start_pm);
					$resume2_date2 = Carbon::parse($date . ' ' . $row_RESUME2->formatted_time);
					$resume2_interval = $resume2_date1->diff($resume2_date2);
					$resume2_difference = ($resume2_interval->h * 60) + $resume2_interval->i;
				}


				$break = "00:00:00";
				if ($in != $break1 && $in_add != $break1) {
					$break = $break1;
				}

				if ($break2 != $resume2) {
					$break = $break2;
				} elseif ($break2_difference < $resume2_difference) {
					$break = $break2;
				}

				$resume = "00:00:00";
				if ($out != $resume1) {
					$resume = $resume1;
				}

				if ($resume2 != $break2) {
					$resume = $resume2;
				} elseif ($break2_difference >= $resume2_difference) {
					$resume = $resume2;
				}


				// ----------------------------------------------------------- CONFIGURE WORK HOUR -----------------------------------------------------------//
				if (($in != '00:00:00' && $break != '00:00:00') || ($in != '00:00:00' && $out != '00:00:00') || ($resume != '00:00:00' && $out != '00:00:00')) {

					// BEGIN TIME
					if ($in <= $row_work_hour->time_start_am && $in != '00:00:00') {
						$begin_time = $row_work_hour->time_start_am;
					} elseif ($in >= $row_work_hour->time_start_am && $in != '00:00:00') {
						$begin_time = $in;
					} elseif ($resume <= $row_work_hour->time_start_pm && $resume != '00:00:00') {
						$begin_time = $row_work_hour->time_start_pm;
					} elseif ($resume >= $row_work_hour->time_start_pm && $resume != '00:00:00') {
						$begin_time = $resume;
					}

					// END TIME
					if ($out >= $row_work_hour->time_end_pm && $out != '00:00:00') {
						$end_time = $row_work_hour->time_end_pm;
					} elseif ($out <= $row_work_hour->time_end_pm && $out != '00:00:00') {
						$end_time = $out;
					} elseif ($break >= $row_work_hour->time_end_am && $break != '00:00:00') {
						$end_time = $row_work_hour->time_end_am;
					} elseif ($break <= $row_work_hour->time_end_am && $break != '00:00:00') {
						$end_time = $break;
					}

					// CALCULATE LUNCH TIME
					if ($begin_time <= $row_work_hour->time_end_am && $end_time >= $row_work_hour->time_start_pm) {
						$lunch_break = Carbon::parse($date . ' ' . $row_work_hour->time_end_am);
						$lunch_resume = Carbon::parse($date . ' ' . $row_work_hour->time_start_pm);
						$lunch_interval = $lunch_break->diff($lunch_resume);
						$lunch_difference = ($lunch_interval->h * 60) + $lunch_interval->i;
					} else {
						$lunch_difference = '0';
					}

					// CALCULATE WORK HOUR
					$begin = Carbon::parse($date . ' ' . $begin_time);
					$end = Carbon::parse($date . ' ' . $end_time);
					$total_interval = $begin->diff($end);
					$total_difference = ($total_interval->h * 60) + $total_interval->i;

					$total_minutes = $total_difference - $lunch_difference;
					$hours = floor($total_minutes / 60);
					$minutes = str_pad(($total_minutes % 60), 2, '0', STR_PAD_LEFT);
					$work_hour = $hours . ":" . $minutes;
				} else {
					$work_hour = '00:00:00';
				}

				// INSERT/UPDATE DATABASE
				$row_Recordset4 = HRAttendance::select('id', 'in', 'break', 'resume', 'out', 'time_work_hour')->where('attend_date', '=', $date)->where('staff_id', '=', $employee['staff_id'])->first();

				if ($row_Recordset4 != NULL) {

					$new_in = $in;
					$new_break = $break;
					$new_resume = $resume;
					$new_out = $out;
					$new_work_hour = $work_hour;


					if ($row_Recordset4->in != '00:00:00' && $in == '00:00:00') {
						$new_in = $row_Recordset4->in;
					} elseif ($row_Recordset4->in != '00:00:00' && $in != '00:00:00' && $row_Recordset4->in <= $in) {
						$new_in = $row_Recordset4->in;
					}

					if ($row_Recordset4->break != '00:00:00' && $break == '00:00:00') {
						$new_break = $row_Recordset4->break;
					} elseif ($row_Recordset4->break != '00:00:00' && $break != '00:00:00' && $row_Recordset4->break <= $break) {
						$new_break = $row_Recordset4->break;
					}

					if ($row_Recordset4->resume != '00:00:00' && $resume == '00:00:00') {
						$new_resume = $row_Recordset4->resume;
					} if ($row_Recordset4->resume != '00:00:00' && $resume != '00:00:00' && $row_Recordset4->resume >= $resume) {
						$new_resume = $row_Recordset4->resume;
					}

					if ($row_Recordset4->out != '00:00:00' && $out == '00:00:00') {
						$new_out = $row_Recordset4->out;
					} elseif ($row_Recordset4->out != '00:00:00' && $out != '00:00:00' && $row_Recordset4->out >= $out) {
						$new_out = $row_Recordset4->out;
					}

					if ($row_Recordset4->time_work_hour != '' && $row_Recordset4->time_work_hour != NULL && $row_Recordset4->time_work_hour >= $work_hour) {
						$new_work_hour = $row_Recordset4->time_work_hour;
					}

					HRAttendance::find($row_Recordset4->id)->update([
						'in' => $new_in,
						'break' => $new_break,
						'resume' => $new_resume,
						'out' => $new_out,
						'time_work_hour' => $new_work_hour,
					]);
				} else {
					HRAttendance::create([
						'staff_id' => $employee['staff_id'],
						'daytype_id' => $daytype_id,
						'attend_date' => $date,
						'in' => $in,
						'break' => $break,
						'resume' => $resume,
						'out' => $out,
						'time_work_hour' => $work_hour,
					]);
				}
			}
		}
		/////////////////////////////////////////////////////////////////////////////////////////
	}
}
