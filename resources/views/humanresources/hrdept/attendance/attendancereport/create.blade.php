@extends('layouts.app')

@section('content')
<?php
// use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\UnavailableDateTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Helpers\TimeCalculator;
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROutstation;

?>
<style>
	@media print {
		body {
			visibility: hidden;
		}

		#printPageButton, #back {
			display: none;
		}

		.table-container {
			visibility: visible;
			position: absolute;
			left: 0;
			top: 0;
		}
	}

	.table-container {
		display: table;
		width: 100%;
		border-collapse: collapse;
	}

	.table {
		display: table;
		width: 100%;
		border-collapse: collapse;
		margin-top: 0;
		padding-top: 0;
		margin-bottom: 0;
		padding-bottom: 0;
	}

	.table-row {
		display: table-row;
	}

	.table-cell {
		display: table-cell;
		border: 1px solid #b3b3b3;
		padding: 4px;
		box-sizing: border-box;
	}

	.table-cell-top {
		display: table-cell;
		border: 1px solid #b3b3b3;
		border-top: none;
		padding: 4px;
		box-sizing: border-box;
	}

	.table-cell-top-bottom {
		display: table-cell;
		border: 1px solid #b3b3b3;
		border-top: none;
		border-bottom: none;
		padding: 0px;
		box-sizing: border-box;
	}

	.table-cell-hidden {
		display: table-cell;
		border: none;
	}

	.header {
		font-size: 22px;
		text-align: center;
	}

	.theme {
		background-color: #e6e6e6;
	}

	.table-cell-top1 {
		display: table-cell;
		border: 1px solid #b3b3b3;
		border-top: none;
		padding: 0px;
		box-sizing: border-box;
	}
</style>
<div class="container table-responsive row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<div class="row g-3">
		<h4>Attendance By Staff</h4>
		<p>&nbsp;</p>
		@if($sa)
			<?php $i = 0; ?>
			@foreach($sa as $v)
				<?php
				$n = 0;
				$ha = \App\Models\HumanResources\HRAttendance::where('staff_id', $v->staff_id)
						->where(function (Builder $query) use ($request){
							$query->whereDate('attend_date', '>=', $request->from)
							->whereDate('attend_date', '<=', $request->to);
						})
						->get();
				?>
			<div class="d-print-table">
				<h5>
					{{ Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username }} {{ Staff::find($v->staff_id)->name }}<br />
					{{ Staff::find($v->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->department }}<br />
					{{ Staff::find($v->staff_id)->belongstorestdaygroup?->group }}
				</h5>
				<table id="attendancestaff_" class="table table-hover table-sm table-bordered align-middle" style="font-size:12px">
					<thead>
						<tr>
							<th scope="col">ID</th>
							<th scope="col">Name</th>
							<th scope="col">Type</th>
							<th scope="col">Cause</th>
							<th scope="col">Leave</th>
							<th scope="col">Date</th>
							<th scope="col">In</th>
							<th scope="col">Break</th>
							<th scope="col">Resume</th>
							<th scope="col">Out</th>
							<th scope="col">Duration</th>
							<th scope="col">Overtime</th>
							<th scope="col">Remarks</th>
							<th scope="col">Exception</th>
						</tr>
					</thead>
					<tbody>
					@foreach($ha as $v1)
<?php
/////////////////////////////
// to determine working hour of each user
$wh = UnavailableDateTime::workinghourtime($v1->attend_date, $v->belongstostaff->id)->first();

// looking for leave of each staff
// $l = $v->belongstostaff->hasmanyleave()
$l = HRLeave::where('staff_id', $v->staff_id)
		->where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})
		->where(function (Builder $query) use ($v1){
			$query->whereDate('date_time_start', '<=', $v1->attend_date)
			->whereDate('date_time_end', '>=', $v1->attend_date);
		})
		->first();

$o = HROvertime::where([['staff_id', $v->staff_id], ['ot_date', $v1->attend_date], ['active', 1]])->first();

$os = HROutstation::where('staff_id', $v->staff_id)
		->where(function (Builder $query) use ($v1){
			$query->whereDate('date_from', '<=', $v1->attend_date)
			->whereDate('date_to', '>=', $v1->attend_date);
		})
		->get();

$in = Carbon::parse($v1->in)->equalTo('00:00:00');
$break = Carbon::parse($v1->break)->equalTo('00:00:00');
$resume = Carbon::parse($v1->resume)->equalTo('00:00:00');
$out = Carbon::parse($v1->out)->equalTo('00:00:00');

// looking for RESTDAY, WORKDAY & HOLIDAY
$sun = Carbon::parse($v1->attend_date)->dayOfWeek == 0;		// sunday
$sat = Carbon::parse($v1->attend_date)->dayOfWeek == 6;		// saturday

$hdate = HRHolidayCalendar::
		where(function (Builder $query) use ($v1){
			$query->whereDate('date_start', '<=', $v1->attend_date)
			->whereDate('date_end', '>=', $v1->attend_date);
		})
		->get();

if($hdate->isNotEmpty()) {											// date holiday
	$dayt = OptDayType::find(3)->daytype;							// show what day: HOLIDAY
	$dtype = false;
} elseif($hdate->isEmpty()) {										// date not holiday
	if(Carbon::parse($v1->attend_date)->dayOfWeek == 0) {			// sunday
		$dayt = OptDayType::find(2)->daytype;
		$dtype = false;
	} elseif(Carbon::parse($v1->attend_date)->dayOfWeek == 6) {		// saturday
		$sat = $v->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $v1->attend_date)->first();
		if($sat) {													// determine if user belongs to sat group restday
			$dayt = OptDayType::find(2)->daytype;					// show what day: RESTDAY
			$dtype = false;
		} else {
			$dayt = OptDayType::find(1)->daytype;					// show what day: WORKDAY
			$dtype = true;
		}
	} else {														// all other day is working day
		$dayt = OptDayType::find(1)->daytype;						// show what day: WORKDAY
		$dtype = true;
	}
}

// detect all
if ($os->isNotEmpty()) {																							// outstation |
	if ($dtype) {																									// outstation | working
		if ($l) {																									// outstation | working | leave
			if ($in) {																								// outstation | working | leave | no in
				if ($break) {																						// outstation | working | leave | no in | no break
					if ($resume) {																					// outstation | working | leave | no in | no break | no resume
						if ($out) {																					// outstation | working | leave | no in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | no in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | leave | no in | no break | resume
						if ($out) {																					// outstation | working | leave | no in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | no in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | working | leave | no in | break
					if ($resume) {																					// outstation | working | leave | no in | break | no resume
						if ($out) {																					// outstation | working | leave | no in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | no in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | leave | no in | break | resume
						if ($out) {																					// outstation | working | leave | no in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | no in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			} else {																								// outstation | working | leave | in
				if ($break) {																						// outstation | working | leave | in | no break
					if ($resume) {																					// outstation | working | leave | in | no break | no resume
						if ($out) {																					// outstation | working | leave | in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | leave | in | no break | resume
						if ($out) {																					// outstation | working | leave | in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | working | leave | in | break
					if ($resume) {																					// outstation | working | leave | in | break | no resume
						if ($out) {																					// outstation | working | leave | in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | leave | in | break | resume
						if ($out) {																					// outstation | working | leave | in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | leave | in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			}
		} else {																									// outstation | working | no leave
			if ($in) {																								// outstation | working | no leave | no in
				if ($break) {																						// outstation | working | no leave | no in | no break
					if ($resume) {																					// outstation | working | no leave | no in | no break | no resume
						if ($out) {																					// outstation | working | no leave | no in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | no in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | no leave | no in | no break | resume
						if ($out) {																					// outstation | working | no leave | no in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | no in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | working | no leave | no in | break
					if ($resume) {																					// outstation | working | no leave | no in | break | no resume
						if ($out) {																					// outstation | working | no leave | no in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | no in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | no leave | no in | break | resume
						if ($out) {																					// outstation | working | no leave | no in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | no in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
								} else {
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			} else {																								// outstation | working | no leave | in
				if ($break) {																						// outstation | working | no leave | in | no break
					if ($resume) {																					// outstation | working | no leave | in | no break | no resume
						if ($out) {																					// outstation | working | no leave | in | no break | no resume | no out
							if (Carbon::parse(now())->gt($v1->attend_date)) {
								if (is_null($v1->attendance_type_id)) {
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
								} else {
									$ll = OptTcms::find($v1->attendance_type_id)->leave;
								}
							} else {
								if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
							}
						} else {																					// outstation | working | no leave | in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | no leave | in | no break | resume
						if ($out) {																					// outstation | working | no leave | in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | working | no leave | in | break
					if ($resume) {																					// outstation | working | no leave | in | break | no resume
						if ($out) {																					// outstation | working | no leave | in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | working | no leave | in | break | resume
						if ($out) {																					// outstation | working | no leave | in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
								} else {
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | working | no leave | in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			}
		}
	} else {																										// outstation | no working
		if ($l) {																									// outstation | no working | leave
			if ($in) {																								// outstation | no working | leave | no in
				if ($break) {																						// outstation | no working | leave | no in | no break
					if ($resume) {																					// outstation | no working | leave | no in | no break | no resume
						if ($out) {																					// outstation | no working | leave | no in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | no in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | leave | no in | no break | resume
						if ($out) {																					// outstation | no working | leave | no in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | no in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | no working | leave | no in | break
					if ($resume) {																					// outstation | no working | leave | no in | break | no resume
						if ($out) {																					// outstation | no working | leave | no in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | no in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | leave | no in | break | resume
						if ($out) {																					// outstation | no working | leave | no in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | no in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			} else {																								// outstation | no working | leave | in
				if ($break) {																						// outstation | no working | leave | in | no break
					if ($resume) {																					// outstation | no working | leave | in | no break | no resume
						if ($out) {																					// outstation | no working | leave | in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | leave | in | no break | resume
						if ($out) {																					// outstation | no working | leave | in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | no working | leave | in | break
					if ($resume) {																					// outstation | no working | leave | in | break | no resume
						if ($out) {																					// outstation | no working | leave | in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | leave | in | break | resume
						if ($out) {																					// outstation | no working | leave | in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | leave | in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			}
		} else {																									// outstation | no working | no leave
			if ($in) {																								// outstation | no working | no leave | no in
				if ($break) {																						// outstation | no working | no leave | no in | no break
					if ($resume) {																					// outstation | no working | no leave | no in | no break | no resume
						if ($out) {																					// outstation | no working | no leave | no in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | no in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | no leave | no in | no break | resume
						if ($out) {																					// outstation | no working | no leave | no in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | no in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | no working | no leave | no in | break
					if ($resume) {																					// outstation | no working | no leave | no in | break | no resume
						if ($out) {																					// outstation | no working | no leave | no in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | no in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | no leave | no in | break | resume
						if ($out) {																					// outstation | no working | no leave | no in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | no in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			} else {																								// outstation | no working | no leave | in
				if ($break) {																						// outstation | no working | no leave | in | no break
					if ($resume) {																					// outstation | no working | no leave | in | no break | no resume
						if ($out) {																					// outstation | no working | no leave | in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | no leave | in | no break | resume
						if ($out) {																					// outstation | no working | no leave | in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// outstation | no working | no leave | in | break
					if ($resume) {																					// outstation | no working | no leave | in | break | no resume
						if ($out) {																					// outstation | no working | no leave | in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// outstation | no working | no leave | in | break | resume
						if ($out) {																					// outstation | no working | no leave | in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// outstation | no working | no leave | in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			}
		}
	}
} else {																											// no outstation
	if ($dtype) {																									// no outstation | working
		if ($l) {																									// no outstation | working | leave
			if ($in) {																								// no outstation | working | leave | no in
				if ($break) {																						// no outstation | working | leave | no in | no break
					if ($resume) {																					// no outstation | working | leave | no in | no break | no resume
						if ($out) {																					// no outstation | working | leave | no in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | no in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					} else {																						// no outstation | working | leave | no in | no break | resume
						if ($out) {																					// no outstation | working | leave | no in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | no in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// no outstation | working | leave | no in | break
					if ($resume) {																					// no outstation | working | leave | no in | break | no resume
						if ($out) {																					// no outstation | working | leave | no in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | no in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					} else {																						// no outstation | working | leave | no in | break | resume
						if ($out) {																					// no outstation | working | leave | no in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | no in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					}
				}
			} else {																								// no outstation | working | leave | in
				if ($break) {																						// no outstation | working | leave | in | no break
					if ($resume) {																					// no outstation | working | leave | in | no break | no resume
						if ($out) {																					// working | leave | in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					} else {																						// no outstation | working | leave | in | no break | resume
						if ($out) {																					// no outstation | working | leave | in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// no outstation | working | leave | in | break
					if ($resume) {																					// no outstation | working | leave | in | break | no resume
						if ($out) {																					// no outstation | working | leave | in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					} else {																						// no outstation | working | leave | in | break | resume
						if ($out) {																					// no outstation | working | leave | in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | leave | in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = $l->belongstooptleavetype?->leave_type_code;
							} else {
								$ll = $v1->belongstoopttcms->leave;
							}
						}
					}
				}
			}
		} else {																									// no outstation | working | no leave
			if ($in) {																								// no outstation | working | no leave | no in
				if ($break) {																						// no outstation | working | no leave | no in | no break
					if ($resume) {																					// no outstation | working | no leave | no in | no break | no resume
						if ($out) {																					// no outstation | working | no leave | no in | no break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(1)->leave.'</a>';					// absent
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation | working | no leave | no in | no break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// no outstation | working | no leave | no in | no break | resume
						if ($out) {																					// no outstation | working | no leave | no in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					//  pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation | working | no leave | no in | no break | resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				} else {																							// no outstation | working | no leave | no in | break
					if ($resume) {																					// no outstation | working | no leave | no in | break | no resume
						if ($out) {																					// no outstation | working | no leave | no in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation |  outstation | working | no leave | no in | break | no resume | out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					} else {																						// no outstation |  outstation | working | no leave | no in | break | resume
						if ($out) {																					// no outstation |  outstation | working | no leave | no in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation |  outstation | working | no leave | no in | break | resume | out
							if (is_null($v1->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
								} else {
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						}
					}
				}
			} else {																								// no outstation |  outstation | working | no leave | in
				if ($break) {																						// no outstation |  outstation | working | no leave | in | no break
					if ($resume) {																					// no outstation |  outstation | working | no leave | in | no break | no resume
						if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | no resume | no out
							if (Carbon::parse(now())->gt($v1->attend_date)) {
								if (is_null($v1->attendance_type_id)) {
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
								} else {
									$ll = OptTcms::find($v1->attendance_type_id)->leave;
								}
							} else {
								$ll = false;
							}
						} else {																					// no outstation |  outstation | working | no leave | in | no break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation |  outstation | working | no leave | in | no break | resume
						if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation |  outstation | working | no leave | in | no break | resume | out
							$ll = false;
						}
					}
				} else {																							// no outstation |  outstation | working | no leave | in | break
					if ($resume) {																					// no outstation |  outstation | working | no leave | in | break | no resume
						if ($out) {																					// no outstation |  outstation | working | no leave | in | break | no resume | no out
							if (is_null($v1->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation | working | no leave | in | break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | working | no leave | in | break | resume
						if ($out) {																					// no outstation | working | no leave | in | break | resume | no out
							if (is_null($v1->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
								} else {
									$ll = '<a href="'.route('attendance.edit', $v1->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = OptTcms::find($v1->attendance_type_id)->leave;
							}
						} else {																					// no outstation | working | no leave | in | break | resume | out
							$ll = false;
						}
					}
				}
			}
		}
	} else {																										// no outstation | no working
		if ($l) {																									// no outstation | no working | leave
			if ($in) {																								// no outstation | no working | leave | no in
				if ($break) {																						// no outstation | no working | leave | no in | no break
					if ($resume) {																					// no outstation | no working | leave | no in | no break | no resume
						if ($out) {																					// no outstation | no working | leave | no in | no break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | no in | no break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | leave | no in | no break | resume
						if ($out) {																					// no outstation | no working | leave | no in | no break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | no in | no break | resume | out
							$ll = false;
						}
					}
				} else {																							// no outstation | no working | leave | no in | break
					if ($resume) {																					// no outstation | no working | leave | no in | break | no resume
						if ($out) {																					// no outstation | no working | leave | no in | break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | no in | break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | leave | no in | break | resume
						if ($out) {																					// no outstation | no working | leave | no in | break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | no in | break | resume | out
							$ll = false;
						}
					}
				}
			} else {																								// no outstation | no working | leave | in
				if ($break) {																						// no outstation | no working | leave | in | no break
					if ($resume) {																					// no outstation | no working | leave | in | no break | no resume
						if ($out) {																					// no outstation | no working | leave | in | no break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | in | no break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | leave | in | no break | resume
						if ($out) {																					// no outstation | no working | leave | in | no break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | in | no break | resume | out
							$ll = false;
						}
					}
				} else {																							// no outstation | no working | leave | in | break
					if ($resume) {																					// no outstation | no working | leave | in | break | no resume
						if ($out) {																					// no outstation | no working | leave | in | break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | in | break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | leave | in | break | resume
						if ($out) {																					// no outstation | no working | leave | in | break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | leave | in | break | resume | out
							$ll = false;
						}
					}
				}
			}
		} else {																									// no outstation | no working | no leave
			if ($in) {																								// no outstation | no working | no leave | no in
				if ($break) {																						// no outstation | no working | no leave | no in | no break
					if ($resume) {																					// no outstation | no working | no leave | no in | no break | no resume
						if ($out) {																					// no outstation | no working | no leave | no in | no break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | no in | no break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | no leave | no in | no break | resume
						if ($out) {																					// no outstation | no working | no leave | no in | no break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | no in | no break | resume | out
							$ll = false;
						}
					}
				} else {																							// no outstation | no working | no leave | no in | break
					if ($resume) {																					// no outstation | no working | no leave | no in | break | no resume
						if ($out) {																					// no outstation | no working | no leave | no in | break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | no in | break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | no leave | no in | break | resume
						if ($out) {																					// no outstation | no working | no leave | no in | break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | no in | break | resume | out
							$ll = false;
						}
					}
				}
			} else {																								// no outstation | no working | no leave | in
				if ($break) {																						// no outstation | no working | no leave | in | no break
					if ($resume) {																					// no outstation | no working | no leave | in | no break | no resume
						if ($out) {																					// no outstation | no working | no leave | in | no break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | in | no break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | no leave | in | no break | resume
						if ($out) {																					// no outstation | no working | no leave | in | no break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | in | no break | resume | out
							$ll = false;
						}
					}
				} else {																							// no outstation | no working | no leave | in | break
					if ($resume) {																					// no outstation | no working | no leave | in | break | no resume
						if ($out) {																					// no outstation | no working | no leave | in | break | no resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | in | break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | no working | no leave | in | break | resume
						if ($out) {																					// no outstation | no working | no leave | in | break | resume | no out
							$ll = false;
						} else {																					// no outstation | no working | no leave | in | break | resume | out
							$ll = false;
						}
					}
				}
			}
		}
	}
}

if($l) {
	$lea = '<a href="'.route('leave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
} else {
	$lea = NULL;
}
// $username = Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()->username;
?>
						<tr class="{{ (Carbon::parse($v1->attend_date)->dayOfWeek == 0)?'table-secondary':NULL }}">
							<td>{{ Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username }}</td>
							<td>{{ Staff::find($v->staff_id)->name }}</td>
							<td>{{ $dayt }}</td>
							<td>{!! $ll !!}</td>
							<td>{!! $lea !!}</td>
							<td>{{ Carbon::parse($v1->attend_date)->format('j M Y') }}</td>
							<td>
								<span class="{{ ($in)?'text-info':((Carbon::parse($v1->in)->gt($wh?->time_start_am))?'text-danger':'') }}">{{ ($in)?'':Carbon::parse($v1->in)->format('g:i a') }}</span>
							</td>
							<td>
								<span class="{{ ($break)?'text-info':((Carbon::parse($v1->break)->lt($wh?->time_end_am))?'text-danger':'') }}">{{ ($break)?'':Carbon::parse($v1->break)->format('g:i a') }}</span>
							</td>
							<td>
								<span class="{{ ($resume)?'text-info':((Carbon::parse($v1->resume)->gt($wh?->time_start_pm))?'text-danger':'') }}">{{ ($resume)?'':Carbon::parse($v1->resume)->format('g:i a') }}</span>
							</td>
							<td><span class="
									<?php
										if($out) {																													// no punch out
											echo 'text-info';
										} else {																													// punch out
											if($o) {																												// punch out | OT
												if (Carbon::parse($v1->out)->gt($o->belongstoovertimerange?->end)) {													// punch out | OT | out lt OT
													echo 'text-d ot';
												} else {																											// punch out | OT | OT lt out
													if (Carbon::parse($v1->out)->lt($o->belongstoovertimerange?->end)) {												// punch out | OT | OT gt out
														echo 'text-danger ot';
													}
												}
											} else {																												// punch out | no OT
												if (Carbon::parse($v1->out)->lt($wh?->time_end_pm)) {																	// punch out | no OT | out lt working hour
													echo 'text-danger wh';
												} else {																											// punch out | no OT | out gt working hour
													if (Carbon::parse($v1->out)->gt($wh?->time_end_pm)) {
														echo 'text-d wh';
													}
												}
											}
										}
									?>
								">
									{{ ($out)?'':Carbon::parse($v1->out)->format('g:i a') }}
								</span></td>
							<td>
								{{ $v1->time_work_hour }}
								<?php
									if (!is_null($v1->time_work_hour)) {
										$m[$i][$n] = Carbon::parse($v1->time_work_hour)->format('H:i:s');
									} else {
										$m[$i][$n] = Carbon::parse('00:00:00')->format('H:i:s');
									}
								?>
							</td>
							<td>
								{{ $o?->belongstoovertimerange?->where('active', 1)->first()?->total_time }}<br />
								<?php
									if (!is_null($o?->belongstoovertimerange?->where('active', 1)->first()?->total_time)) {
										$p[$i][$n] = Carbon::parse($o?->belongstoovertimerange?->where('active', 1)->first()?->total_time)->format('H:i:s');
									} else {
										$p[$i][$n] = Carbon::parse('00:00:00')->format('H:i:s');
									}
								?>
							</td>
							<td class="w-25">{{ $v1->remarks }} <br /><span class="text-danger">{{ $v1->hr_remarks }}</span> </td>
							<td>{{ $v1->exception }}</td>
						</tr>
						<?php $n++; ?>
					@endforeach
						<tr>
							<td colspan="10" rowspan="1"></td>
							<td><strong class="text-success">{{ TimeCalculator::total_time($m[$i]) }}</strong></td>
							<td><strong class="text-success">{{ TimeCalculator::total_time($p[$i]) }}</strong></td>
							<td colspan="2" rowspan="1"></td>
						</tr>
					</tbody>
				</table>
			</div>
				<?php $i++; ?>
			@endforeach
		@else
		@endif
	</div>




</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// datepicker
$('#from').datetimepicker({
	icons: {
		time: "fas fas-regular fa-clock fa-beat",
		date: "fas fas-regular fa-calendar fa-beat",
		up: "fa-regular fa-circle-up fa-beat",
		down: "fa-regular fa-circle-down fa-beat",
		previous: 'fas fas-regular fa-arrow-left fa-beat',
		next: 'fas fas-regular fa-arrow-right fa-beat',
		today: 'fas fas-regular fa-calenday-day fa-beat',
		clear: 'fas fas-regular fa-broom-wide fa-beat',
		close: 'fas fas-regular fa-rectangle-xmark fa-beat'
	},
	format: 'YYYY-MM-DD',
	useCurrent: true,
})
.on('dp.change dp.update', function(e) {

});

$('#to').datetimepicker({
	icons: {
		time: "fas fas-regular fa-clock fa-beat",
		date: "fas fas-regular fa-calendar fa-beat",
		up: "fa-regular fa-circle-up fa-beat",
		down: "fa-regular fa-circle-down fa-beat",
		previous: 'fas fas-regular fa-arrow-left fa-beat',
		next: 'fas fas-regular fa-arrow-right fa-beat',
		today: 'fas fas-regular fa-calenday-day fa-beat',
		clear: 'fas fas-regular fa-broom-wide fa-beat',
		close: 'fas fas-regular fa-rectangle-xmark fa-beat'
	},
	format: 'YYYY-MM-DD',
	useCurrent: true,
})
.on('dp.change dp.update', function(e) {

});

/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
// $(document).ready(function(){
// 	$('[data-bs-toggle="tooltip"]').tooltip();
// });

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#attendancestaff').DataTable({
	"columnDefs": [
					{ type: 'date', 'targets': [3] },
					{ type: 'time', 'targets': [4] },
					{ type: 'time', 'targets': [5] },
					{ type: 'time', 'targets': [6] },
					{ type: 'time', 'targets': [7] },
				],
	"lengthMenu": [ [-1], ["All"] ],
	"order": [[3, "asc" ]],	// sorting the 6th column descending
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
@endsection
