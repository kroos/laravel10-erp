@extends('layouts.app')

@section('content')
<?php
// use \App\Models\HumanResources\HRLeaveAnnual;
use \App\Models\Staff;
use \App\Models\HumanResources\HRLeave;
use \App\Models\HumanResources\OptLeaveType;

use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Unpaid Medical Certificate Leave</h4>
	<div class="col-sm-12 table-responsive row m-3">
		<table class="table table-hover table-sm" id="active" style="font-size:12px">
		@foreach($upls as $tp)
			<thead>
				<tr>
					<th class="text-primary" colspan="8">Unpaid Leave Entitlement {{ $tp->ryear }}</th>
				</tr>
				<?php
				$uplss = HRLeave::join('logins', 'hr_leaves.staff_id', '=', 'logins.staff_id')
							->where('leave_type_id', 11)
							->whereYear('date_time_start', $tp->ryear)
							->where(function(Builder $query) {
								$query->whereIn('leave_status_id', [5, 6])
								->orWhereNull('leave_status_id');
							})
							->groupBy('hr_leaves.staff_id')
							->orderBy('logins.username', 'ASC')
							->get();
							// ->ddRawSql();
				?>
				@foreach($uplss as $value)
					<tr>
						<th class="text-success" colspan="8">Unpaid Leave Entitlement {{ $tp->ryear }} For {{ $value->username }} {{ Staff::find($value->staff_id)?->name }}</th>
					</tr>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Leave ID</th>
						<th>Leave Type</th>
						<th>Duration</th>
						<th>From</th>
						<th>To</th>
						<th>Remarks</th>
					</tr>
				</thead>
				<?php
				$uplsss = HRLeave::where('leave_type_id', 11)
							->whereYear('date_time_start', $tp->ryear)
							->where(function(Builder $query) {
								$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
							})
							->where('staff_id', $value->staff_id)
							->orderBy('hr_leaves.date_time_start', 'DESC')
							->get();
							// ->ddrawsql();
				$dur = 0;
				?>
				@foreach($uplsss as $t)
				<tbody>
					<tr>
						<td>{{ $t->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $t->belongstostaff?->name }}">
							{{ Str::words($t->belongstostaff?->name, 3, ' >') }}
						</td>
						<td>
							<a href="{{ route('hrleave.show', $t->id) }}" target="_blank">
								HR9-{{ str_pad($t->leave_no, 5, "0", STR_PAD_LEFT) }}/{{ $t->leave_year }}
							</a>
						</td>
						<td>{{ OptLeaveType::find($t->leave_type_id)->leave_type_code }}</td>
						<td>
							{{ $t->period_day }} day/s
							<?php $dur += $t->period_day ?>
						</td>
						<td>{{ \Carbon\Carbon::parse($t->date_time_start)->format('j M Y') }}</td>
						<td>{{ \Carbon\Carbon::parse($t->date_time_end)->format('j M Y') }}</td>
						<td {!! ($t->reason)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$t->reason.'"':NULL !!}>
							{{  Str::limit($t->reason, 10, ' >') }}
						</td>
					</tr>
				</tbody>
				@endforeach
				<tfoot>
					<tr>
						<th colspan="3"></th>
						<th>Total</th>
						<th>{{ $dur }} day/'s</th>
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
	paging: false,
	lengthMenu: [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
	columnDefs: [
					{ type: 'date', 'targets': [5,6] },
					// { type: 'time', 'targets': [6] },
				],
	order: [ 5, 'desc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
