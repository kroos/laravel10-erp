@extends('layouts.app')

@section('content')
<?php
// use \App\Models\HumanResources\HRLeaveAnnual;
use \App\Models\Staff;
use \App\Models\HumanResources\HRAttendance;
use \App\Models\HumanResources\OptTcms;

use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Staff Absent Record</h4>
	<div class="col-sm-12 table-responsive row m-3">
		<table class="table table-hover table-sm" id="active" style="font-size:12px">
		@foreach($absents as $tp)
			<thead>
				<tr>
					<th class="text-primary" colspan="8">Staff Absent On Year {{ $tp->ayear }}</th>
				</tr>
				<?php
				$absentss = HRAttendance::join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
								->where('logins.active', 1)
								->whereIn('attendance_type_id', [1,2])
								->whereYear('attend_date', $tp->ayear)
								->groupBy('hr_attendances.staff_id')
								->orderBy('logins.username', 'ASC')
								->orderBy('attend_date', 'DESC')
								->get();
								// ->ddRawSql();
				?>
				@foreach($absentss as $value)
					<tr>
						<th class="text-success" colspan="8">Unpaid Leave Entitlement {{ $tp->ryear }} For {{ $value->username }} {{ Staff::find($value->staff_id)?->name }}</th>
					</tr>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Date</th>
						<th>Absent</th>
						<th>Leave</th>
						<th>Outstation</th>
						<th>Remarks</th>
					</tr>
				</thead>
				<?php
				$absentsss = HRAttendance::where('hr_attendances.staff_id', $value->staff_id)
								->whereIn('attendance_type_id', [1,2])
								->whereYear('attend_date', $tp->ayear)
								// ->orderBy('logins.username', 'ASC')
								->orderBy('attend_date', 'DESC')
								->get();
								// ->ddRawSql();
				$dur = 0;
				?>
				@foreach($absentsss as $t)
				<tbody>
					<tr>
						<td>{{ $value->username }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $t->belongstostaff?->name }}">
							{{ Str::words($t->belongstostaff?->name, 3, ' >') }}
						</td>
						<td>{{ \Carbon\Carbon::parse($t->attend_date)->format('j M Y') }}</td>
						<td>
							{{ OptTcms::find($t->attendance_type_id)->leave_short }}
							<?php
								if ($t->attendance_type_id == 1) {
									$durr = 1;
								} elseif ($t->attendance_type_id == 2) {
									$durr = 0.5;
								}
								$dur += $durr;
							?>
						</td>
						<td>
							@if($t->leave_id)
								<a href="{{ route('hrleave.show', $t->leave_id) }}" target="_blank">
									HR9-{{ str_pad($t->belongstoleave->leave_no, 5, "0", STR_PAD_LEFT) }}/{{ $t->belongstoleave->leave_year }}
								</a>
							@endif
						</td>
						<td>
							@if($t->outstation_id)
								{{ $t->belongstocustomer?->customer }}
							@endif
						</td>
						<td {!! ($t->remarks || $t->hr_remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$t->remarks.'<br />'.$t->hr_remarks.'"':NULL !!}>
							{{ Str::limit($t->remarks, 8, ' >') }}
							<br />
							<span class="text-danger">
								{{ Str::limit($t->hr_remarks, 8, ' >') }}
							</span>
						</td>
					</tr>
				</tbody>
				@endforeach
				<tfoot>
					<tr>
						<th colspan="2"></th>
						<th>Total</th>
						<th>{{ $dur }} day/s</th>
						<th colspan="3"></th>
					</tr>
				</tfoot>
			@endforeach
		@endforeach
		</table>
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
$('#inactive,#active').DataTable({
	"paging": false,
	"lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [5,6] },
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
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
