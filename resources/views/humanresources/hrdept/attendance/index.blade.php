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

// load model
use App\Helpers\UnavailableDateTime;

// load lib
use \Carbon\Carbon;

// who am i?
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

?>
			@if($ha)
<?php
/////////////////////////////
// to determine working hour of each user
$wh = UnavailableDateTime::workinghourtime($s->attend_date, $s->belongstostaff->id)->first();

// looking for leave of each staff
$l = $s->belongstostaff->hasmanyleave()
		->where(function (Builder $query) {
			$query->where('leave_status_id', 5)->orWhereNull('leave_status_id');
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
$sun = Carbon::parse($s->attend_date)->dayOfWeek == 7;
$sat = Carbon::parse($s->attend_date)->dayOfWeek == 6;
$hdate = HRHolidayCalendar::
		where(function (Builder $query) use ($s){
			$query->whereDate('date_start', '<=', $s->attend_date)
			->whereDate('date_end', '>=', $s->attend_date);
		})
		->get();
if($hdate->isNotEmpty()) {											// date holiday
	$dayt = OptDayType::find(3)->daytype;							// camouflage for temp before update the table
	$s->update(['daytype_id' => 3]);
	$dtype = false;
} elseif($hdate->isEmpty()) {										// date not holiday
	if(Carbon::parse($s->attend_date)->dayOfWeek == 0) {			// sunday
		$dayt = OptDayType::find(2)->daytype;
		$s->update(['daytype_id' => 2]);
		$dtype = false;
	} elseif(Carbon::parse($s->attend_date)->dayOfWeek == 6) {		// saturday
		$sat = $s->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $s->attend_date)->first();
		// dd($sat);
		if($sat) {													// determine if user belongs to sat group restday
			$dayt = OptDayType::find(2)->daytype;
			$s->update(['daytype_id' => 2]);
			$dtype = false;
		} else {
			$dayt = OptDayType::find(1)->daytype;
			$s->update(['daytype_id' => 1]);
			$dtype = true;
		}
	} else {														// all other day is working day
		$dayt = OptDayType::find(1)->daytype;
		$s->update(['daytype_id' => 1]);
		$dtype = true;
	}
}

$o = HROvertime::where([['staff_id', $s->belongstostaff->id], ['ot_date', $s->attend_date]])->first();
// dump($o);
if($o) {
	$ot = $o;
}

// detect absent
if( (($in && $break && $resume && $break) && $dtype) && !$l ) {
	if(is_null($s->attendance_type_id)) {
		$ll = OptTcms::find(1)->leave;
		// $s->update(['attendance_type_id' => 1]);
	} else {
		$ll = $s->belongstoopttcms->leave;
	}
} else {																							// check sini
	if( (($break && ($resume && $out)) && $dtype) && !$l ) {
		$ll = OptTcms::find(2)->leave;
		// $s->update(['attendance_type_id' => 2]);
	}
}

// || ($in && $break) || ($resume && $out)
// $ll = false;





if ($dtype) {													// working
	if ($l) {													// working | leave
		if ($in) {												// working | leave | no in
			if ($break) {										// working | leave | no in | no break
				if ($resume) {									// working | leave | no in | no break | no resume
					if ($out) {									// working | leave | no in | no break | no resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | no in | no break | no resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				} else {										// working | leave | no in | no break | resume
					if ($out) {									// working | leave | no in | no break | resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | no in | no break | resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				}
			} else {											// working | leave | no in | break
				if ($resume) {									// working | leave | no in | break | no resume
					if ($out) {									// working | leave | no in | break | no resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | no in | break | no resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				} else {										// working | leave | no in | break | resume
					if ($out) {									// working | leave | no in | break | resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | no in | break | resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				}
			}
		} else {												// working | leave | in
			if ($break) {										// working | leave | in | no break
				if ($resume) {									// working | leave | in | no break | no resume
					if ($out) {									// working | leave | in | no break | no resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | in | no break | no resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				} else {										// working | leave | in | no break | resume
					if ($out) {									// working | leave | in | no break | resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | in | no break | resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				}
			} else {											// working | leave | in | break
				if ($resume) {									// working | leave | in | break | no resume
					if ($out) {									// working | leave | in | break | no resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | in | break | no resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				} else {										// working | leave | in | break | resume
					if ($out) {									// working | leave | in | break | resume | no out
						$ll = $l->belongstooptleavetype->leave_type_code;
					} else {									// working | leave | in | break | resume | out
						$ll = $l->belongstooptleavetype->leave_type_code;
					}
				}
			}
		}
	} else {													// working | no leave
		if ($in) {												// working | no leave | no in
			if ($break) {										// working | no leave | no in | no break
				if ($resume) {									// working | no leave | no in | no break | no resume
					if ($out) {									// working | no leave | no in | no break | no resume | no out
						$ll = OptTcms::find(1)->leave;			// absent
					} else {									// working | no leave | no in | no break | no resume | out
						$ll = OptTcms::find(2)->leave;			// half absent
					}
				} else {										// working | no leave | no in | no break | resume
					if ($out) {									// working | no leave | no in | no break | resume | no out
						$ll = 'Check';							// pls check
					} else {									// working | no leave | no in | no break | resume | out
						$ll = OptTcms::find(2)->leave;			// half absent
					}
				}
			} else {											// working | no leave | no in | break
				if ($resume) {									// working | no leave | no in | break | no resume
					if ($out) {									// working | no leave | no in | break | no resume | no out
						$ll = 'Check';							// pls check
					} else {									// working | no leave | no in | break | no resume | out
						$ll = 'Check';							// pls check
					}
				} else {										// working | no leave | no in | break | resume
					if ($out) {									// working | no leave | no in | break | resume | no out
						$ll = 'Check';							// pls check
					} else {									// working | no leave | no in | break | resume | out
						$ll = 'Check';							// pls check
					}
				}
			}
		} else {												// working | no leave | in
			if ($break) {										// working | no leave | in | no break
				if ($resume) {									// working | no leave | in | no break | no resume
					if ($out) {									// working | no leave | in | no break | no resume | no out
						if (Carbon::parse(now())->gt($s->attend_date)) {
							$ll = OptTcms::find(2)->leave;			// half absent
						} else {
							$ll = false;
						}
					} else {									// working | no leave | in | no break | no resume | out
						if($ot) {

						}
						$ll = false;
					}
				} else {										// working | no leave | in | no break | resume
					if ($out) {									// working | no leave | in | no break | resume | no out
						$ll = 'Check';							// pls check
					} else {									// working | no leave | in | no break | resume | out
						$ll = false;
					}
				}
			} else {											// working | no leave | in | break
				if ($resume) {									// working | no leave | in | break | no resume
					if ($out) {									// working | no leave | in | break | no resume | no out
						$ll = OptTcms::find(2)->leave;			// half absent
					} else {									// working | no leave | in | break | no resume | out
						$ll = false;
					}
				} else {										// working | no leave | in | break | resume
					if ($out) {									// working | no leave | in | break | resume | no out
						$ll = 'Check';							// pls check
					} else {									// working | no leave | in | break | resume | out
						$ll = false;
					}
				}
			}
		}
	}
} else {														// no working
	if ($l) {													// no working | leave
		if ($in) {												// no working | leave | no in
			if ($break) {										// no working | leave | no in | no break
				if ($resume) {									// no working | leave | no in | no break | no resume
					if ($out) {									// no working | leave | no in | no break | no resume | no out
						$ll = false;
					} else {									// no working | leave | no in | no break | no resume | out
						$ll = false;
					}
				} else {										// no working | leave | no in | no break | resume
					if ($out) {									// no working | leave | no in | no break | resume | no out
						$ll = false;
					} else {									// no working | leave | no in | no break | resume | out
						$ll = false;
					}
				}
			} else {											// no working | leave | no in | break
				if ($resume) {									// no working | leave | no in | break | no resume
					if ($out) {									// no working | leave | no in | break | no resume | no out
						$ll = false;
					} else {									// no working | leave | no in | break | no resume | out
						$ll = false;
					}
				} else {										// no working | leave | no in | break | resume
					if ($out) {									// no working | leave | no in | break | resume | no out
						$ll = false;
					} else {									// no working | leave | no in | break | resume | out
						$ll = false;
					}
				}
			}
		} else {												// no working | leave | in
			if ($break) {										// no working | leave | in | no break
				if ($resume) {									// no working | leave | in | no break | no resume
					if ($out) {									// no working | leave | in | no break | no resume | no out
						$ll = false;
					} else {									// no working | leave | in | no break | no resume | out
						$ll = false;
					}
				} else {										// no working | leave | in | no break | resume
					if ($out) {									// no working | leave | in | no break | resume | no out
						$ll = false;
					} else {									// no working | leave | in | no break | resume | out
						$ll = false;
					}
				}
			} else {											// no working | leave | in | break
				if ($resume) {									// no working | leave | in | break | no resume
					if ($out) {									// no working | leave | in | break | no resume | no out
						$ll = false;
					} else {									// no working | leave | in | break | no resume | out
						$ll = false;
					}
				} else {										// no working | leave | in | break | resume
					if ($out) {									// no working | leave | in | break | resume | no out
						$ll = false;
					} else {									// no working | leave | in | break | resume | out
						$ll = false;
					}
				}
			}
		}
	} else {													// no working | no leave
		if ($in) {												// no working | no leave | no in
			if ($break) {										// no working | no leave | no in | no break
				if ($resume) {									// no working | no leave | no in | no break | no resume
					if ($out) {									// no working | no leave | no in | no break | no resume | no out
						$ll = false;
					} else {									// no working | no leave | no in | no break | no resume | out
						$ll = false;
					}
				} else {										// no working | no leave | no in | no break | resume
					if ($out) {									// no working | no leave | no in | no break | resume | no out
						$ll = false;
					} else {									// no working | no leave | no in | no break | resume | out
						$ll = false;
					}
				}
			} else {											// no working | no leave | no in | break
				if ($resume) {									// no working | no leave | no in | break | no resume
					if ($out) {									// no working | no leave | no in | break | no resume | no out
						$ll = false;
					} else {									// no working | no leave | no in | break | no resume | out
						$ll = false;
					}
				} else {										// no working | no leave | no in | break | resume
					if ($out) {									// no working | no leave | no in | break | resume | no out
						$ll = false;
					} else {									// no working | no leave | no in | break | resume | out
						$ll = false;
					}
				}
			}
		} else {												// no working | no leave | in
			if ($break) {										// no working | no leave | in | no break
				if ($resume) {									// no working | no leave | in | no break | no resume
					if ($out) {									// no working | no leave | in | no break | no resume | no out
						$ll = false;
					} else {									// no working | no leave | in | no break | no resume | out
						$ll = false;
					}
				} else {										// no working | no leave | in | no break | resume
					if ($out) {									// no working | no leave | in | no break | resume | no out
						$ll = false;
					} else {									// no working | no leave | in | no break | resume | out
						$ll = false;
					}
				}
			} else {											// no working | no leave | in | break
				if ($resume) {									// no working | no leave | in | break | no resume
					if ($out) {									// no working | no leave | in | break | no resume | no out
						$ll = false;
					} else {									// no working | no leave | in | break | no resume | out
						$ll = false;
					}
				} else {										// no working | no leave | in | break | resume
					if ($out) {									// no working | no leave | in | break | resume | no out
						$ll = false;
					} else {									// no working | no leave | in | break | resume | out
						$ll = false;
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
?>
				<tr>
					<td>{{ $s->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username }}</td>
					<td>{{ $s->name }}</td>
					<td>{{ $dayt }}</td>
					<td>{{ $ll }}</td>
					<td>{!! $lea !!}</td>
					<td>{{ Carbon::parse($s->attend_date)->format('j M Y') }}</td>
					<td><span class="{{ ($in)?'text-info':((Carbon::parse($s->in)->gt($wh->time_start_am))?'text-danger':'') }}">{{ ($in)?'':Carbon::parse($s->in)->format('g:i a') }}</span></td>
					<td><span class="{{ ($break)?'text-info':((Carbon::parse($s->break)->lt($wh->time_end_am))?'text-danger':'') }}">{{ ($break)?'':Carbon::parse($s->break)->format('g:i a') }}</span></td>
					<td><span class="{{ ($resume)?'text-info':((Carbon::parse($s->resume)->gt($wh->time_start_pm))?'text-danger':'') }}">{{ ($resume)?'':Carbon::parse($s->resume)->format('g:i a') }}</span></td>
					<td><span class="{{ ($out)?'text-info':((Carbon::parse($s->out)->lt($wh->time_end_pm))?'text-danger':'') }}">{{ ($out)?'':Carbon::parse($s->out)->format('g:i a') }}</span></td>
					<td>{{ $s->time_work_hour }}</td>
					<td>{{ $s->remarks }}</td>
					<td>{{ $s->exception }}</td>
				</tr>
<a href=""></a>
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
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#attendance').DataTable({
	"paging": false,
	"lengthMenu": [ [-1], ["All"] ],
	"order": [[ 0, 'asc' ], [ 1, 'asc' ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection
