@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Attendance</h4>
	<div class="">
		<div class="d-flex justify-content-center">
			{!! $sa->links() !!} <!-- check this for this type of pagination -->
		</div>
		<table id="attendance" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Type</th>
					<th>Cause</th>
					<th>Leave</th>
					<th>Date</th>
					<th>In</th>
					<th>Break</th>
					<th>Resume</th>
					<th>Out</th>
					<th>Duration</th>
					<th>Overtime</th>
					<th>Remarks</th>
					<th>Exception</th>
				</tr>
			</thead>
			<tbody>
<?php
// load facade
use Illuminate\Database\Eloquent\Builder;

// load helper
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROutstation;

// load helper
use App\Helpers\UnavailableDateTime;

// load lib
use \Carbon\Carbon;

// who am i? ppl who can see only his staff in same department
$me1 = \Auth::user()->belongstostaff->div_id == 1;		// hod
$me2 = \Auth::user()->belongstostaff->div_id == 5;		// hod assistant
$me3 = \Auth::user()->belongstostaff->div_id == 4;		// supervisor
// $me4 = \Auth::user()->belongstostaff->div_id == 3;		// HR
$me5 = \Auth::user()->belongstostaff->authorise_id == 1;	// admin
$me6 = \Auth::user()->belongstostaff->div_id == 2;		// director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;
$i = 1;
?>
			@foreach($attendance as $s)
<?php
// dump($s);

// setting for authorised views
if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 16 || $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}
// done setting for authorised view for hod, asst hod, supervisor, director and hr
?>
			@if($ha)
<?php
/////////////////////////////
// to determine working hour of each user
$wh = UnavailableDateTime::workinghourtime($s->attend_date, $s->belongstostaff->id)->first();

// looking for leave of each staff
$l = $s->belongstostaff->hasmanyleave()
		->where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})
		->where(function (Builder $query) use ($s){
			$query->whereDate('date_time_start', '<=', $s->attend_date)
			->whereDate('date_time_end', '>=', $s->attend_date);
		})
		->first();
// dump($l);
$in = Carbon::parse($s->in)->equalTo('00:00:00');
$break = Carbon::parse($s->break)->equalTo('00:00:00');
$resume = Carbon::parse($s->resume)->equalTo('00:00:00');
$out = Carbon::parse($s->out)->equalTo('00:00:00');

// looking for RESTDAY, WORKDAY & HOLIDAY
$sun = Carbon::parse($s->attend_date)->dayOfWeek == 0;		// sunday
$sat = Carbon::parse($s->attend_date)->dayOfWeek == 6;		// saturday
$hdate = HRHolidayCalendar::
		where(function (Builder $query) use ($s){
			$query->whereDate('date_start', '<=', $s->attend_date)
			->whereDate('date_end', '>=', $s->attend_date);
		})
		->get();

if($hdate->isNotEmpty()) {											// date holiday
	$dayt = OptDayType::find(3)->daytype;							// show what day: HOLIDAY
	$dtype = false;
	$s->update(['daytype_id' => 3]);
} elseif($hdate->isEmpty()) {										// date not holiday
	if(Carbon::parse($s->attend_date)->dayOfWeek == 0) {			// sunday
		$dayt = OptDayType::find(2)->daytype;
		$dtype = false;
		$s->update(['daytype_id' => 2]);
	} elseif(Carbon::parse($s->attend_date)->dayOfWeek == 6) {		// saturday
		$sat = $s->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $s->attend_date)->first();
		if($sat) {													// determine if user belongs to sat group restday
			$dayt = OptDayType::find(2)->daytype;					// show what day: RESTDAY
			$dtype = false;
			$s->update(['daytype_id' => 2]);
		} else {
			$dayt = OptDayType::find(1)->daytype;					// show what day: WORKDAY
			$dtype = true;
			$s->update(['daytype_id' => 1]);
		}
	} else {														// all other day is working day
		$dayt = OptDayType::find(1)->daytype;						// show what day: WORKDAY
		$dtype = true;
		$s->update(['daytype_id' => 1]);
	}
}

$o = HROvertime::where([['staff_id', $s->belongstostaff->id], ['ot_date', $s->attend_date], ['active', 1]])->first();
// dump($o);

// looking for outstation
// checking for outstation
$os = HROutstation::where('staff_id', $s->belongstostaff->id)
		->where(function (Builder $query) use ($s){
			$query->whereDate('date_from', '<=', $s->attend_date)
			->whereDate('date_to', '>=', $s->attend_date);
		})
		->get();

// dump($os);

// detect all
if ($os->isNotEmpty()) {																							// outstation |
	if ($dtype) {																									// outstation | working
		if ($l) {																									// outstation | working | leave
			if ($in) {																								// outstation | working | leave | no in
				if ($break) {																						// outstation | working | leave | no in | no break
					if ($resume) {																					// outstation | working | leave | no in | no break | no resume
						if ($out) {																					// outstation | working | leave | no in | no break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | no in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | leave | no in | no break | resume
						if ($out) {																					// outstation | working | leave | no in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | no in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | working | leave | no in | break
					if ($resume) {																					// outstation | working | leave | no in | break | no resume
						if ($out) {																					// outstation | working | leave | no in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | no in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | leave | no in | break | resume
						if ($out) {																					// outstation | working | leave | no in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | no in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				}
			} else {																								// outstation | working | leave | in
				if ($break) {																						// outstation | working | leave | in | no break
					if ($resume) {																					// outstation | working | leave | in | no break | no resume
						if ($out) {																					// outstation | working | leave | in | no break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | leave | in | no break | resume
						if ($out) {																					// outstation | working | leave | in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | working | leave | in | break
					if ($resume) {																					// outstation | working | leave | in | break | no resume
						if ($out) {																					// outstation | working | leave | in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | leave | in | break | resume
						if ($out) {																					// outstation | working | leave | in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | leave | in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
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
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | no in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | no leave | no in | no break | resume
						if ($out) {																					// outstation | working | no leave | no in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | no in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | working | no leave | no in | break
					if ($resume) {																					// outstation | working | no leave | no in | break | no resume
						if ($out) {																					// outstation | working | no leave | no in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | no in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | no leave | no in | break | resume
						if ($out) {																					// outstation | working | no leave | no in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | no in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
									} else {
									$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				}
			} else {																								// outstation | working | no leave | in
				if ($break) {																						// outstation | working | no leave | in | no break
					if ($resume) {																					// outstation | working | no leave | in | no break | no resume
						if ($out) {																					// outstation | working | no leave | in | no break | no resume | no out
							if (Carbon::parse(now())->gt($s->attend_date)) {
								if (is_null($s->attendance_type_id)) {
									$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
									} else {
									$ll = $s->belongstoopttcms->leave;
								}
							} else {
								if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
							}
						} else {																					// outstation | working | no leave | in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | no leave | in | no break | resume
						if ($out) {																					// outstation | working | no leave | in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | working | no leave | in | break
					if ($resume) {																					// outstation | working | no leave | in | break | no resume
						if ($out) {																					// outstation | working | no leave | in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | working | no leave | in | break | resume
						if ($out) {																					// outstation | working | no leave | in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
									} else {
									$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | working | no leave | in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
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
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | no in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | leave | no in | no break | resume
						if ($out) {																					// outstation | no working | leave | no in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | no in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | no working | leave | no in | break
					if ($resume) {																					// outstation | no working | leave | no in | break | no resume
						if ($out) {																					// outstation | no working | leave | no in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | no in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | leave | no in | break | resume
						if ($out) {																					// outstation | no working | leave | no in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | no in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				}
			} else {																								// outstation | no working | leave | in
				if ($break) {																						// outstation | no working | leave | in | no break
					if ($resume) {																					// outstation | no working | leave | in | no break | no resume
						if ($out) {																					// outstation | no working | leave | in | no break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | leave | in | no break | resume
						if ($out) {																					// outstation | no working | leave | in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | no working | leave | in | break
					if ($resume) {																					// outstation | no working | leave | in | break | no resume
						if ($out) {																					// outstation | no working | leave | in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | leave | in | break | resume
						if ($out) {																					// outstation | no working | leave | in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | leave | in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
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
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | no in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | no leave | no in | no break | resume
						if ($out) {																					// outstation | no working | no leave | no in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | no in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | no working | no leave | no in | break
					if ($resume) {																					// outstation | no working | no leave | no in | break | no resume
						if ($out) {																					// outstation | no working | no leave | no in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | no in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | no leave | no in | break | resume
						if ($out) {																					// outstation | no working | no leave | no in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | no in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				}
			} else {																								// outstation | no working | no leave | in
				if ($break) {																						// outstation | no working | no leave | in | no break
					if ($resume) {																					// outstation | no working | no leave | in | no break | no resume
						if ($out) {																					// outstation | no working | no leave | in | no break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | no leave | in | no break | resume
						if ($out) {																					// outstation | no working | no leave | in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// outstation | no working | no leave | in | break
					if ($resume) {																					// outstation | no working | no leave | in | break | no resume
						if ($out) {																					// outstation | no working | no leave | in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// outstation | no working | no leave | in | break | resume
						if ($out) {																					// outstation | no working | no leave | in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// outstation | no working | no leave | in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(4)->leave.'</a>';					// outstation
							} else {
								$ll = $s->belongstoopttcms->leave;
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
							$ll = $l->belongstooptleavetype?->leave_type_code;
						} else {																					// no outstation | working | leave | no in | no break | no resume | out
							$ll = $l->belongstooptleavetype?->leave_type_code;
						}
					} else {																						// no outstation | working | leave | no in | no break | resume
						if ($out) {																					// no outstation | working | leave | no in | no break | resume | no out
							$ll = $l->belongstooptleavetype?->leave_type_code;
						} else {																					// no outstation | working | leave | no in | no break | resume | out
							$ll = $l->belongstooptleavetype?->leave_type_code;
						}
					}
				} else {																							// no outstation | working | leave | no in | break
					if ($resume) {																					// no outstation | working | leave | no in | break | no resume
						if ($out) {																					// no outstation | working | leave | no in | break | no resume | no out
							$ll = $l->belongstooptleavetype->leave_type_code;
						} else {																					// no outstation | working | leave | no in | break | no resume | out
							$ll = $l->belongstooptleavetype->leave_type_code;
						}
					} else {																						// no outstation | working | leave | no in | break | resume
						if ($out) {																					// no outstation | working | leave | no in | break | resume | no out
							$ll = $l->belongstooptleavetype->leave_type_code;
						} else {																					// no outstation | working | leave | no in | break | resume | out
							$ll = $l->belongstooptleavetype->leave_type_code;
						}
					}
				}
			} else {																								// no outstation | working | leave | in
				if ($break) {																						// no outstation | working | leave | in | no break
					if ($resume) {																					// no outstation | working | leave | in | no break | no resume
						if ($out) {																					// no outstation | working | leave | in | no break | no resume | no out
							$ll = $l->belongstooptleavetype->leave_type_code;
						} else {																					// no outstation | working | leave | in | no break | no resume | out
							$ll = $l->belongstooptleavetype->leave_type_code;
						}
					} else {																						// no outstation | working | leave | in | no break | resume
						if ($out) {																					// no outstation | working | leave | in | no break | resume | no out
							$ll = $l->belongstooptleavetype->leave_type_code;
						} else {																					// no outstation | working | leave | in | no break | resume | out
							$ll = $l->belongstooptleavetype->leave_type_code;
						}
					}
				} else {																							// no outstation | working | leave | in | break
					if ($resume) {																					// no outstation | working | leave | in | break | no resume
						if ($out) {																					// no outstation | working | leave | in | break | no resume | no out
							$ll = $l->belongstooptleavetype->leave_type_code;
						} else {																					// no outstation | working | leave | in | break | no resume | out
							$ll = $l->belongstooptleavetype->leave_type_code;
						}
					} else {																						// no outstation | working | leave | in | break | resume
						if ($out) {																					// no outstation | working | leave | in | break | resume | no out
							$ll = $l->belongstooptleavetype->leave_type_code;
						} else {																					// no outstation | working | leave | in | break | resume | out
							$ll = $l->belongstooptleavetype->leave_type_code;
						}
					}
				}
			}
		} else {																									// no outstation | working | no leave
			if ($in) {																								// no outstation | working | no leave | no in
				if ($break) {																						// no outstation | working | no leave | no in | no break
					if ($resume) {																					// no outstation | working | no leave | no in | no break | no resume
						if ($out) {																					// no outstation | working | no leave | no in | no break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(1)->leave.'</a>';					// absent
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | no leave | no in | no break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// no outstation | working | no leave | no in | no break | resume
						if ($out) {																					// no outstation | working | no leave | no in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					//  pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | no leave | no in | no break | resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				} else {																							// no outstation | working | no leave | no in | break
					if ($resume) {																					// no outstation | working | no leave | no in | break | no resume
						if ($out) {																					// no outstation | working | no leave | no in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// no outstation |  outstation | working | no leave | no in | break | no resume | out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					} else {																						// no outstation |  outstation | working | no leave | no in | break | resume
						if ($out) {																					// no outstation |  outstation | working | no leave | no in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// no outstation |  outstation | working | no leave | no in | break | resume | out
							if (is_null($s->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
								} else {
									$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						}
					}
				}
			} else {																								// no outstation |  outstation | working | no leave | in
				if ($break) {																						// no outstation |  outstation | working | no leave | in | no break
					if ($resume) {																					// no outstation |  outstation | working | no leave | in | no break | no resume
						if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | no resume | no out
							if (Carbon::parse(now())->gt($s->attend_date)) {
								if (is_null($s->attendance_type_id)) {
									$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
								} else {
									$ll = $s->belongstoopttcms->leave;
								}
							} else {
								$ll = false;
							}
						} else {																					// no outstation |  outstation | working | no leave | in | no break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation |  outstation | working | no leave | in | no break | resume
						if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// no outstation |  outstation | working | no leave | in | no break | resume | out
							$ll = false;
						}
					}
				} else {																							// no outstation |  outstation | working | no leave | in | break
					if ($resume) {																					// no outstation |  outstation | working | no leave | in | break | no resume
						if ($out) {																					// no outstation |  outstation | working | no leave | in | break | no resume | no out
							if (is_null($s->attendance_type_id)) {
								$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
							} else {
								$ll = $s->belongstoopttcms->leave;
							}
						} else {																					// no outstation | working | no leave | in | break | no resume | out
							$ll = false;
						}
					} else {																						// no outstation | working | no leave | in | break | resume
						if ($out) {																					// no outstation | working | no leave | in | break | resume | no out
							if (is_null($s->attendance_type_id)) {
								if ($break == $resume) {															// check for break and resume is the same value
									$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
								} else {
									$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
								}
							} else {
								$ll = $s->belongstoopttcms->leave;
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
	if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
		$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.str_pad($l->leave_no,5,'0',STR_PAD_LEFT).'/'.$l->leave_year.'</a>';
		$s->update(['leave_id' => $l->id]);
	} else {															// otherwise just show the leave
		// $lea = $s->belongstoleave->id;
		$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT).'/'.$s->belongstoleave->leave_year.'</a>';
	}
} else {
	$lea = NULL;
	$s->update(['leave_id' => NULL]);
}
?>
				<tr>
					<td>
						<a href="{{ route('attendance.edit', $s->id) }}">{{ $s->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username }}</a>
					</td>
					<td>{{ $s->name }}</td>
					<td>{{ $dayt }}</td>
					<td>{!! $ll !!}</td>
					<td>{!! $lea !!}</td>
					<td>{{ Carbon::parse($s->attend_date)->format('j M Y') }}</td>
					<td><span class="{{ ($in)?'text-info':((Carbon::parse($s->in)->gt($wh->time_start_am))?'text-danger':'') }}">{{ ($in)?'':Carbon::parse($s->in)->format('g:i a') }}</span></td>
					<td><span class="{{ ($break)?'text-info':((Carbon::parse($s->break)->lt($wh->time_end_am))?'text-danger':'') }}">{{ ($break)?'':Carbon::parse($s->break)->format('g:i a') }}</span></td>
					<td><span class="{{ ($resume)?'text-info':((Carbon::parse($s->resume)->gt($wh->time_start_pm))?'text-danger':'') }}">{{ ($resume)?'':Carbon::parse($s->resume)->format('g:i a') }}</span></td>
					<td><span class="
							<?php
								if($out) {																													// no punch out
									echo 'text-info';
								} else {																													// punch out
									if($o) {																												// punch out | OT
										if (Carbon::parse($s->out)->gt($o->belongstoovertimerange?->end)) {													// punch out | OT | out lt OT
											echo 'text-d ot';
										} else {																											// punch out | OT | OT lt out
											if (Carbon::parse($s->out)->lt($o->belongstoovertimerange?->end)) {												// punch out | OT | OT gt out
												echo 'text-danger ot';
											}
										}
									} else {																												// punch out | no OT
										if (Carbon::parse($s->out)->lt($wh->time_end_pm)) {																	// punch out | no OT | out lt working hour
											echo 'text-danger wh';
										} else {																											// punch out | no OT | out gt working hour
											if (Carbon::parse($s->out)->gt($wh->time_end_pm)) {
												echo 'text-d wh';
											}
										}
									}
								}
							?>
						">
							{{ ($out)?'':Carbon::parse($s->out)->format('g:i a') }}
						</span></td>
					<td>{{ $s->time_work_hour }}</td>
					<td>{{ $o?->belongstoovertimerange?->where('active', 1)->first()->total_time }}</td>
					<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($s->remarks)??' ' }} | {{ ($s->hr_remarks)??' ' }}">{{ Str::limit($s->remarks, 8, ' >') }} <span class="text-danger">{{ Str::limit($s->hr_remarks, 8, ' >') }}</td>
					<td>{{ $s->exception }}</td>
				</tr>
			@endif
			@endforeach
			</tbody>
		</table>
	</div>
	<div class="d-flex justify-content-center">
		{!! $sa->links() !!} <!-- check this for this type of pagination -->
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#attendance').DataTable({
	"paging": false,
	"lengthMenu": [ [-1], ["All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [5] },
					{ type: 'time', 'targets': [6] },
					{ type: 'time', 'targets': [7] },
					{ type: 'time', 'targets': [8] },
					{ type: 'time', 'targets': [9] },
				],
	"order": [[ 0, 'asc' ], [ 1, 'asc' ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection
