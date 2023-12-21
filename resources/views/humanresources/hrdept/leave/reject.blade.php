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
<div class="container row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<h4>Leaves</h4>
	<p>&nbsp;</p>
	<h5>Reject Leaves</h5>
	@if($reject)
	<div class="col-sm-12 table-responsive">
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
					<th>Others Approval</th>
					<th>Leave Remarks</th>
					<th>Leave Remarks HR</th>
				</tr>
			</thead>
			<tbody>
				@foreach($reject as $ul)
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
	if ($deptid == 21 || $deptid == 28) {																// hod | dept prod A | dept prod B
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7 || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 3;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 16 || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7 || Staff::find($ul->staff_id)?->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 3;
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
							<td><a href="{{ route('staff.show', $ul->staff_id) }}" target="_blank">{{ App\Models\Login::where([['staff_id', $ul->staff_id], ['active', 1]])->first()->username ?? NULL }}</a></td>
							<td {!!  ($ul->staff_id)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->belongstostaff?->name.'"':null !!}>
								{{ Str::words($ul->belongstostaff?->name, 3, ' >') }}
							</td>
							<td><a href="{{ route('hrleave.show', $ul->id) }}" target="_blank">HR9-{{ str_pad( $ul->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $ul->leave_year }}</a></td>
							<td>{{ $ul->belongstooptleavetype?->leave_type_code }}</td>
							<td>{{ Carbon::parse($ul->created_at)->format('j M Y') }}</td>
							<td>{{ $dts }}</td>
							<td>{{ $dte }}</td>
							<td>{{ $dper }}</td>
							<td {!! ($ul->reason)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->reason.'"':null !!}>
								{{ Str::limit($ul->reason, 10, ' >') }}
							</td>
							<td>
								@if(is_null($ul->leave_status_id))
									Pending
								@else
									{{ $ul->belongstooptleavestatus?->status }}
								@endif
							</td>
							<td>


								<table class="table table-hover table-sm">
									<tbody>
										@if($ul->hasmanyleaveapprovalbackup()->get()->isNotEmpty())
											<tr>
												<!-- <td>Backup {{ $ul->hasmanyleaveapprovalbackup()->first()->belongstostaff?->name }}</td> -->
												<th>Backup</th>
												<td>{{ $ul->hasmanyleaveapprovalbackup()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td {!! ($ul->hasmanyleaveapprovalbackup()->first()?->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->hasmanyleaveapprovalbackup()->first()?->remarks.'"':null !!}>{{ Str::limit($ul->hasmanyleaveapprovalbackup()->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovalsupervisor()->get()->isNotEmpty())
											<tr>
												<!-- <td>Supervisor {{ $ul->hasmanyleaveapprovalsupervisor()->first()->belongstostaff?->name }}</td> -->
												<th>Supervisor</th>
												<td>{{ $ul->hasmanyleaveapprovalsupervisor()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td {!! ($ul->hasmanyleaveapprovalsupervisor()->first()?->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->hasmanyleaveapprovalsupervisor()->first()?->remarks.'"':null !!}>{{ Str::limit($ul->hasmanyleaveapprovalsupervisor()->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovalhod()->get()->isNotEmpty())
											<tr>
												<!-- <td>HOD {{ $ul->hasmanyleaveapprovalhod()->first()->belongstostaff?->name }}</td> -->
												<th>HOD</th>
												<td>{{ $ul->hasmanyleaveapprovalhod()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td {!! ($ul->hasmanyleaveapprovalhod()->first()?->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->hasmanyleaveapprovalhod()->first()?->remarks.'"':null !!}>{{ Str::limit($ul->hasmanyleaveapprovalhod()->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovaldir()->get()->isNotEmpty())
											<tr>
												<!-- <td>Director {{ $ul->hasmanyleaveapprovaldir()->first()->belongstostaff?->name }}</td> -->
												<th>Director</th>
												<td>{{ $ul->hasmanyleaveapprovaldir()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td {!! ($ul->hasmanyleaveapprovaldir()->first()?->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->hasmanyleaveapprovaldir()->first()?->remarks.'"':null !!}>{{ Str::limit($ul->hasmanyleaveapprovaldir()->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif

										@if($ul->hasmanyleaveapprovalhr()->get()->isNotEmpty())
											<tr>
												<!-- <td>HR {{ $ul->hasmanyleaveapprovalhr()->first()->belongstostaff?->name }}</td> -->
												<th>HR</th>
												<td>{{ $ul->hasmanyleaveapprovalhr()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
												<th>Remarks</th>
												<td {!! ($ul->hasmanyleaveapprovalhr()->first()?->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->hasmanyleaveapprovalhr()->first()?->remarks.'"':null !!}>{{ Str::limit($ul->hasmanyleaveapprovalhr()->first()?->remarks, 7, ' >>') }}</td>
											</tr>
										@endif
									</tbody>
								</table>

















								@if($ul->softcopy)
									<!-- <a href="{{ asset('storage/leaves/'.$ul->softcopy) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-richtext"></i></a> -->
								@else
									<!-- Button trigger modal -->
									<!-- <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploaddoc_{{ $ul->id }}">
										<i class="fa-solid fa-upload"></i>
									</button> -->

									<!-- Modal -->
									<!-- <div class="modal fade" id="uploaddoc_{{ $ul->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploaddocLabel_{{ $ul->id }}" aria-hidden="true">
										<div class="modal-dialog">
											<div class="modal-content">
												{{ Form::open(['route' => ['uploaddoc', $ul->id], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
												<div class="modal-header">
													<h1 class="modal-title fs-5" id="uploaddocLabel_{{ $ul->id }}">Upload Supporting Document</h1>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body text-center">

													<div class="form-group row m-2 {{ $errors->has('document') ? 'has-error' : '' }}">
														{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}
														<div class="col-sm-8">
															{{ Form::file( 'document', ['class' => 'form-control form-control-sm form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}
														</div>
													</div>

													<div class="form-group row m-2 {{ $errors->has('amend_note') ? 'has-error' : '' }}">
														{{ Form::label( 'rem', 'Remarks : ', ['class' => 'col-sm-4 col-form-label'] ) }}
														<div class="col-sm-8">
															{{ Form::textarea( 'amend_note', @$value, ['class' => 'form-control form-control-sm', 'id' => 'rem', 'placeholder' => 'Remarks']) }}
														</div>
													</div>

												</div>
												<div class="modal-footer">
														<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
														{{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
												</div>
												{{ Form::close() }}
											</div>
										</div>
									</div> -->
								@endif
							</td>
							<td {!! ($ul->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->remarks.'"':null !!}>
								{{ Str::limit($ul->remarks, 10, ' >') }}
							</td>
							<td {!! ($ul->hasmanyleaveamend()->first()?->amend_note)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$ul->hasmanyleaveamend()->first()?->amend_note.'"':null !!}>
								{{ Str::limit($ul->hasmanyleaveamend()->first()?->amend_note, 10, ' >') }}
							</td>
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
	</div>
	@else
	<p>No Rejected Leave</p>
	@endif
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
	// "paging": false,
	"lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
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
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
