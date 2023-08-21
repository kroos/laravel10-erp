@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Attendance</h4>
	<div class="table-responsive">
		<table id="attendance" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Type</th>
					<th>Leave</th>
					<th>Date</th>
					<th>In</th>
					<th>Break</th>
					<th>Resume</th>
					<th>Out</th>
					<th>Duration</th>
					<th>Exception</th>
				</tr>
			</thead>
			<tbody>
<?php
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
?>
			@foreach($attendance as $s)
<?php
if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 16 || $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || ($s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstostaff?->where('active', 1)->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
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
			@if($s->belongstostaff?->active == 1)
				<tr>
					<td>{{ $s->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username }}</td>
					<td>{{ $s->belongstostaff?->name }}</td>
					<td>{{ $s->belongstodaytype?->daytype }}</td>
					<td>{{ $s->belongstoopttcms?->leave }}</td>
					<td>{{ Carbon::parse($s->attend_date)->format('j M Y') }}</td>
					<td>{{ Carbon::parse($s->in)->format('g:i a') }}</td>
					<td>{{ Carbon::parse($s->break)->format('g:i a') }}</td>
					<td>{{ Carbon::parse($s->resume)->format('g:i a') }}</td>
					<td>{{ Carbon::parse($s->out)->format('g:i a') }}</td>
					<td>{{ $s->time_work_hour }}</td>
				</tr>
			@endif
			@endif
			@endforeach
			</tbody>
		</table>
	</div>
	<div class="d-flex justify-content-center">
		{!! $attendance->links() !!}
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
$('#staff').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"order": [[1, "asc" ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection
