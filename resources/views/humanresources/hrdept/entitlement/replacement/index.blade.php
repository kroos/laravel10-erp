@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRLeaveReplacement;
use \App\Models\HumanResources\HRLeaveAnnual;

use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Replacement Leave Entitlement</h4>
	<div class="col-sm-12 table-responsive row m-3">
		<table class="table table-hover table-sm" id="active" style="font-size:12px">
		@foreach($replacements as $tp)
			<thead>
				<tr>
					<th class="text-center" colspan="8">Replacement Leave Entitlement ({{ $tp->ryear }}) for Active Staff</th>
				</tr>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Reason</th>
					<th>Location</th>
					<th>Replacement Leave</th>
					<th>Replacement Leave Utilize</th>
					<th>Replacement Leave Balance</th>
					<th>Remarks</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			@foreach(HRLeaveReplacement::whereYear('date_start', $tp->ryear)->orderBy('date_start', 'DESC')->get() as $t)
				@if($t->belongstostaff->active == 1)
					<tr>
						<td>{{ $t->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
						<td>{{ $t->belongstostaff->name }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $t->reason }}">
							{{ Str::limit($t->reason, 10, ' >') }}
						</td>
						<td>{{ $t->customer_id }}</td>
						<td>{{ $t->leave_total }} day/s</td>
						<td>{{ $t->leave_utilize }} day/s</td>
						<td>{{ $t->leave_balance }} day/s</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $t->remarks }}">
							{{  Str::limit($t->remarks, 10, ' >') }}
						</td>
						<td class="table-responsive">
							<?php
							$leaves = $t->belongstomanyleave()->where(function(Builder $query) {
											$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
										})
										->get();
							?>
							@if($leaves->count())
								<table class="table table-hover table-sm">
									<thead>
										<tr>
											<th>Leave ID</th>
											<th>Duration</th>
										</tr>
									</thead>
									<tbody>
										<?php $total = 0; ?>
										@foreach($leaves as $key => $leave)
											<tr>
												<td>
													<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
												</td>
												<td>
													{{ $leave->period_day }} day/s
													<?php $total += $leave->period_day; ?>
												</td>
											</tr>
										@endforeach
									</tbody>
									<tfoot>
										<tr>
											<td>Total</td>
											<td>{{ $total }} day/s</td>
										</tr>
									</tfoot>
								</table>
							@endif
						</td>
					</tr>
				@endif
			@endforeach
			</tbody>
		@endforeach
		</table>
	</div>
	<div class="col-sm-12 table-responsive row m-3">
		<table class="table table-hover table-sm" id="inactive" style="font-size:12px">
		@foreach(HRLeaveReplacement::whereYear('date_start', $tp->ryear)->orderBy('date_start', 'DESC')->get() as $tp)
			<thead>
				<tr>
					<th class="text-center" colspan="8">Replacement Leave Entitlement ({{ $tp->ryear }}) For Inactive Staff</th>
				</tr>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Replacement Leave</th>
					<th>Replacement Leave Utilize</th>
					<th>Replacement Leave Balance</th>
					<th>Remarks</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			@foreach(HRLeaveAnnual::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
				@if($t->belongstostaff->active <> 1)
					<tr>
						<td>{{ $t->belongstostaff->hasmanylogin()->first()?->username }}</td>
						<td>{{ $t->belongstostaff->name }}</td>
						<td>{{ $t->leave_total }} day/s</td>
						<td>{{ $t->leave_utilize }} day/s</td>
						<td>{{ $t->leave_balance }} day/s</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $t->remarks }}">
							{{  Str::limit($t->remarks, 10, ' >') }}
						</td>
						<td class="table-responsive">
							<?php
							$leaves = $t->belongstomanyleave()->where(function(Builder $query) {
											$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
										})
										->get();
							?>
							@if($leaves->count())
								<table class="table table-hover table-sm">
									<thead>
										<tr>
											<th>Leave ID</th>
											<th>Duration</th>
										</tr>
									</thead>
									<tbody>
										<?php $total = 0; ?>
										@foreach($leaves as $key => $leave)
											<tr>
												<td>
													<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
												</td>
												<td>
													{{ $leave->period_day }} day/s
													<?php $total += $leave->period_day; ?>
												</td>
											</tr>
										@endforeach
									</tbody>
									<tfoot>
										<tr>
											<td>Total</td>
											<td>{{ $total }} day/s</td>
										</tr>
									</tfoot>
								</table>
							@endif
						</td>
					</tr>
				@endif
			@endforeach
			</tbody>
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
	"lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
	// "columnDefs": [
	// 				{ type: 'date', 'targets': [4,5,6] },
	// 				// { type: 'time', 'targets': [6] },
	// 			],
	"order": [ 0, 'asc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
