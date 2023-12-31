<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load laravel-Excel
// use App\Exports\PayslipExport;
// use Maatwebsite\Excel\Facades\Excel;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\AttendanceJob;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

use App\Models\Login;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROvertimeRange;

// load helper
use App\Helpers\TimeCalculator;
use App\Helpers\UnavailableDateTime;

// load paginator
// use Illuminate\Pagination\Paginator;

// load cursor pagination
// use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Log;
use Exception;

class AttendanceExcelReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|2|5,14|31', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()/*: View*/
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request): View
	{
		return view('humanresources.hrdept.attendance.attendanceexcelreport.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)/*: RedirectResponse*/
	{
		$validated = $request->validate(
			[
				'from' => 'required|date_format:Y-m-d|before_or_equal:to',
				'to' => 'required|date_format:Y-m-d|after_or_equal:from',
			],
			[
				'from.required' => 'Please insert date from',
				'to.required' => 'Please insert date to',
			],
			[
				'from' => 'From Date',
				'to' => 'To Date',
			]
		);

		$from = Carbon::parse($request->from)->format('j F Y');
		$to = Carbon::parse($request->to)->format('j F Y');

		// get staff which is in attendance for a particular date
		$hratt = HRAttendance::select('staff_id')
				->where(function (Builder $query) use ($request) {
					$query->whereDate('attend_date', '>=', $request->from)
						->whereDate('attend_date', '<=', $request->to);
				})
				->groupBy('staff_id')
				// ->ddrawsql();
				->get();

		$header[-1] = ['Emp No', 'Name', 'AL', 'NRL', 'MC', 'UPL', 'Absent', 'UPMC', 'Lateness(minute)', 'Early Out(minute)', 'No Pay Hour', 'Maternity', 'Hospitalization', 'Other Leave', 'Compassionate Leave', 'Marriage Leave', 'Day Work', '1.0 OT', '1.5 OT', 'OT', 'TF'];
		// $records = [];

		// loop staff from attendance => total staff
		foreach ($hratt as $k1 => $v1) {
			$login = Login::where([['staff_id', $v1->staff_id], ['active', 1]])->first()?->username;
			$name = Staff::find($v1->staff_id)->name;

			// find leave in attendance
			$sattendances = HRAttendance::where(function (Builder $query) use ($request) {
					$query->whereDate('attend_date', '>=', $request->from)
						->whereDate('attend_date', '<=', $request->to);
				})
				->where('staff_id', $v1->staff_id)
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
		$combine = $header + $records;
		// dd(collect($combine));
		// return collect($combine);
		$handle = fopen(storage_path('app/public/excel/payslip.csv'), 'w');
		foreach ($combine as $value) {
			fputcsv($handle, $value);
		}
		fclose($handle);






















		// return Excel::download(new PayslipExport($request->only(['from', 'to'])), $from.' - '.$to.' AttendancePayRoll.xlsx');
		// return redirect()->route('excelreport.create', ['id' => $batch->id, 'request' => $request]);
		return redirect()->route('excelreport.create');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $hRAttendance): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $hRAttendance): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRAttendance $hRAttendance): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $hRAttendance): JsonResponse
	{
		//
	}
}
