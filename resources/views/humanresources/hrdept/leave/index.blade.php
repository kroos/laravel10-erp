@extends('layouts.app')

@section('content')
<?php
// load models
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\HRLeaveReplacement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRLeaveApprovalHOD;
use App\Models\HumanResources\HRLeaveApprovalDirector;
use App\Models\HumanResources\HRLeaveApprovalHR;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load sql builder
use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;

use \App\Helpers\UnavailableDateTime;

$upleave = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
			->whereDate('date_time_start', '>', now())
			// ->where(function (Builder $query) {
			// 	$query->whereDate('date_time_start', '<=', $s->attend_date)
			// 	->whereDate('date_time_end', '>=', $s->attend_date);
			// })
			->orderBy('date_time_start', 'DESC')
			->get();
			// ->ddRawSql();
$toleave = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
			// ->whereDate('date_time_start', '>', now())
			->where(function (Builder $query) {
				$query->whereDate('date_time_start', '<=', now())
				->whereDate('date_time_end', '>=', now());
			})
			->orderBy('date_time_start', 'DESC')
			->get();
			// ->ddRawSql();
$paleave = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
			->where(function (Builder $query) {
				$query->whereDate('date_time_end', '<', now())
				->whereDate('date_time_start', '>=', now()->startOfYear());
			})
			->orderBy('date_time_end', 'DESC')
			->get();
			// ->ddRawSql();



// who am i?
$me1 = \Auth::user()->belongstostaff->div_id == 1;		// hod
$me2 = \Auth::user()->belongstostaff->div_id == 5;		// hod assistant
$me3 = \Auth::user()->belongstostaff->div_id == 4;		// supervisor
$me4 = \Auth::user()->belongstostaff->div_id == 3;		// HR
$me5 = \Auth::user()->belongstostaff->authorise_id == 1;	// admin
$me6 = \Auth::user()->belongstostaff->div_id == 2;		// director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;
?>
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Leaves</h4>
	<p>&nbsp;</p>
	<h5>Upcoming Leaves</h5>
	@if($upleave)
		<table id="upleave" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Leave ID</th>
					<th>Type</th>
					<th>Date Applied</th>
					<th>From</th>
					<th>To</th>
					<th>Duration</th>
					<th>Reason</th>
					<th>Status</th>
					<th>Remarks</th>
					<th>Remarks HR</th>
				</tr>
			</thead>
			<tbody>
				@foreach($upleave as $ul)
<?php
if ( ($ul->leave_type_id == 9) || ($ul->leave_type_id != 9 && $ul->half_type_id == 2) || ($ul->leave_type_id != 9 && $ul->half_type_id == 1) ) {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y g:i a');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y g:i a');

	if ($ul->leave_type_id != 9) {
		if ($ul->half_type_id == 2) {
			$dper = $ul->period_day.' Day';
		} elseif($ul->half_type_id == 1) {
			$dper = $ul->period_day.' Day';
		}
	}elseif ($ul->leave_type_id == 9) {
		$i = \Carbon\Carbon::parse($ul->period_time);
		$dper = $i->hour.' hour, '.$i->minute.' minutes';
	}

} else {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y ');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y ');
	$dper = $ul->period_day.' day/s';
}

if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 16 || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || (Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || (Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}
?>
					@if( $ha )
						<tr>
							<td><a href="{{ route('staff.show', $ul->staff_id) }}">{{ App\Models\Login::where([['staff_id', $ul->staff_id], ['active', 1]])->first()->username ?? NULL }}</a></td>
							<td>{{ $ul->belongstostaff?->name }}</td>
							<td><a href="{{ route('hrleave.show', $ul->id) }}">HR9-{{ str_pad( $ul->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $ul->leave_year }}</a></td>
							<td>{{ $ul->belongstooptleavetype?->leave_type_code }}</td>
							<td>{{ Carbon::parse($ul->created_at)->format('j M Y') }}</td>
							<td>{{ $dts }}</td>
							<td>{{ $dte }}</td>
							<td>{{ $dper }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $ul->reason }}">{{ Str::limit($ul->reason, 10, ' >') }}</td>
							<td>
								@if(is_null($ul->leave_status_id))
									Pending
								@else
									{{ $ul->belongstooptleavestatus?->status }}
								@endif
							</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->remarks)??' ' }}">{{ Str::limit($ul->remarks, 10, ' >') }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->hasmanyleaveamend()->first()?->amend_note)??' ' }}">{{ Str::limit($ul->hasmanyleaveamend()->first()?->amend_note, 10, ' >') }}</td>
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
	@else
	<p>No Upcoming Leave</p>
	@endif

	<p>&nbsp;</p>
	<h5>Current Leaves</h5>
	@if($toleave)
		<table id="toleave" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Leave ID</th>
					<th>Type</th>
					<th>Date Applied</th>
					<th>From</th>
					<th>To</th>
					<th>Duration</th>
					<th>Reason</th>
					<th>Status</th>
					<th>Remarks</th>
					<th>Remarks HR</th>
				</tr>
			</thead>
			<tbody>
				@foreach($toleave as $ul)
<?php
if ( ($ul->leave_type_id == 9) || ($ul->leave_type_id != 9 && $ul->half_type_id == 2) || ($ul->leave_type_id != 9 && $ul->half_type_id == 1) ) {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y g:i a');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y g:i a');

	if ($ul->leave_type_id != 9) {
		if ($ul->half_type_id == 2) {
			$dper = $ul->period_day.' Day';
		} elseif($ul->half_type_id == 1) {
			$dper = $ul->period_day.' Day';
		}
	}elseif ($ul->leave_type_id == 9) {
		$i = \Carbon\Carbon::parse($ul->period_time);
		$dper = $i->hour.' hour, '.$i->minute.' minutes';
	}

} else {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y ');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y ');
	$dper = $ul->period_day.' day/s';
}

if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 16 || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || (Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || (Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}
?>
					@if( $ha )
						<tr>
							<td><a href="{{ route('staff.show', $ul->staff_id) }}">{{ App\Models\Login::where([['staff_id', $ul->staff_id], ['active', 1]])->first()->username ?? NULL }}</a></td>
							<td>{{ $ul->belongstostaff?->name }}</td>
							<td><a href="{{ route('hrleave.show', $ul->id) }}">HR9-{{ str_pad( $ul->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $ul->leave_year }}</a></td>
							<td>{{ $ul->belongstooptleavetype?->leave_type_code }}</td>
							<td>{{ Carbon::parse($ul->created_at)->format('j M Y') }}</td>
							<td>{{ $dts }}</td>
							<td>{{ $dte }}</td>
							<td>{{ $dper }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $ul->reason }}">{{ Str::limit($ul->reason, 10, ' >') }}</td>
							<td>
								@if(is_null($ul->leave_status_id))
									Pending
								@else
									{{ $ul->belongstooptleavestatus?->status }}
								@endif
							</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->remarks)??' ' }}">{{ Str::limit($ul->remarks, 10, ' >') }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->hasmanyleaveamend()->first()?->amend_note)??' ' }}">{{ Str::limit($ul->hasmanyleaveamend()->first()?->amend_note, 10, ' >') }}</td>
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
	@else
	<p>No Current Leave</p>
	@endif

<p>&nbsp;</p>
	<h5>Past Leaves</h5>
	@if($paleave)
		<table id="paleave" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Leave ID</th>
					<th>Type</th>
					<th>Date Applied</th>
					<th>From</th>
					<th>To</th>
					<th>Duration</th>
					<th>Reason</th>
					<th>Status</th>
					<th>Remarks</th>
					<th>Remarks HR</th>
				</tr>
			</thead>
			<tbody>
				@foreach($paleave as $ul)
<?php
if ( ($ul->leave_type_id == 9) || ($ul->leave_type_id != 9 && $ul->half_type_id == 2) || ($ul->leave_type_id != 9 && $ul->half_type_id == 1) ) {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y g:i a');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y g:i a');

	if ($ul->leave_type_id != 9) {
		if ($ul->half_type_id == 2) {
			$dper = $ul->period_day.' Day';
		} elseif($ul->half_type_id == 1) {
			$dper = $ul->period_day.' Day';
		}
	}elseif ($ul->leave_type_id == 9) {
		$i = \Carbon\Carbon::parse($ul->period_time);
		$dper = $i->hour.' hour, '.$i->minute.' minutes';
	}

} else {
	$dts = \Carbon\Carbon::parse($ul->date_time_start)->format('j M Y ');
	$dte = \Carbon\Carbon::parse($ul->date_time_end)->format('j M Y ');
	$dper = $ul->period_day.' day/s';
}

if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 16 || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || (Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || (Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}
?>
					@if( $ha )
						<tr>
							<td><a href="{{ route('staff.show', $ul->staff_id) }}">{{ App\Models\Login::where([['staff_id', $ul->staff_id], ['active', 1]])->first()->username ?? NULL }}</a></td>
							<td>{{ $ul->belongstostaff?->name }}</td>
							<td><a href="{{ route('hrleave.show', $ul->id) }}">HR9-{{ str_pad( $ul->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $ul->leave_year }}</a></td>
							<td>{{ $ul->belongstooptleavetype?->leave_type_code }}</td>
							<td>{{ Carbon::parse($ul->created_at)->format('j M Y') }}</td>
							<td>{{ $dts }}</td>
							<td>{{ $dte }}</td>
							<td>{{ $dper }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $ul->reason }}">{{ Str::limit($ul->reason, 10, ' >') }}</td>
							<td>
								@if(is_null($ul->leave_status_id))
									Pending
								@else
									{{ $ul->belongstooptleavestatus?->status }}
								@endif
							</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->remarks)??' ' }}">{{ Str::limit($ul->remarks, 10, ' >') }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($ul->hasmanyleaveamend()->first()?->amend_note)??' ' }}">{{ Str::limit($ul->hasmanyleaveamend()->first()?->amend_note, 10, ' >') }}</td>
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
	@else
	<p>No Past Leave</p>
	@endif

	<p>&nbsp;</p>
	<div id="calendar"></div>
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
$('#upleave').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [4,5,6] },
					// { type: 'time', 'targets': [6] },
				],
	"order": [ 5, 'desc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

$('#toleave').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [4,5,6] },
					// { type: 'time', 'targets': [6] },
				],
	"order": [ 5, 'desc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

$('#paleave').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [4,5,6] },
					// { type: 'time', 'targets': [6] },
				],
	"order": [ 5, 'desc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
// fullcalendar cant use jquery
document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');
	var calendar = new FullCalendar.Calendar(calendarEl, {
		aspectRatio: 1.0,
		initialView: 'dayGridMonth',
		weekNumbers: true,
		themeSystem: 'bootstrap',
		events: {
			url: '{{ route('leaveevents') }}',
			method: 'POST',
			extraParams: {
				_token: '{!! csrf_token() !!}',
			},
		},
		failure: function() {
			alert('There was an error while fetching leaves!');
		},
	});
	calendar.render();
	console.log(calendar.getOption('aspectRatio'));
});

/////////////////////////////////////////////////////////////////////////////////////////

@endsection
