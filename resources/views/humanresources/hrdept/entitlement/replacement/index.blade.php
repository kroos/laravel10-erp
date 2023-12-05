@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRLeaveReplacement;
use \App\Models\HumanResources\HRLeaveAnnual;
use \App\Models\Staff;
use \App\Models\Customer;

use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Replacement Leave Entitlement</h4>
	<div class="col-sm-12 table-responsive row">
		<table class="table table-hover table-sm text-wrap" id="active" style="font-size:12px">
		@foreach($replacements as $tp)
			<thead>
				<tr>
					<th class="text-center text-success" colspan="9">Replacement Leave Entitlement ({{ $tp->ryear }})</th>
				</tr>
			<?php
			$rp = HRLeaveReplacement::join('logins', 'hr_leave_replacements.staff_id', '=', 'logins.staff_id')->whereYear('date_start', $tp->ryear)->groupBy('hr_leave_replacements.staff_id')->orderBy('logins.username', 'ASC')->orderBy('hr_leave_replacements.date_start', 'DESC')->get();
			?>
			@foreach($rp as $t)
				<tr>
					<th colspan="9">&nbsp;</th>
				</tr>
				<tr>
					<th class="text-primary" colspan="9">Replacement Leave Entitlement ({{ $tp->ryear }}) for {{ $t->username }} {{ Staff::find($t->staff_id)->name }}</th>
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
					<th>Leave</th>
				</tr>
			</thead>
			<tbody>
				<?php $totalreplacement = 0; ?>
				@foreach(HRLeaveReplacement::whereYear('date_start', $tp->ryear)->where('staff_id', $t->staff_id)->orderBy('date_start', 'DESC')->get() as $value)
					<tr>
						<td>{{ $value->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
						<td>{{ $value->belongstostaff->name }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $value->reason }}">
							{{ Str::limit($value->reason, 10, ' >') }}
						</td>
						<td>{{ Customer::find($value->customer_id)?->customer }}</td>
						<td>{{ $value->leave_total }} day/s</td>
						<td>{{ $value->leave_utilize }} day/s</td>
						<td>
							{{ $value->leave_balance }} day/s
							<?php $totalreplacement += $value->leave_balance ?>
						</td>
						<td {!! ($value->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$value->remarks.'"':NULL !!}>
							{{  Str::limit($value->remarks, 10, ' >') }}
						</td>
						<td class="table-responsive">
							<?php
							$leaves = $value->belongstomanyleave()->where(function(Builder $query) {
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
													<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">
														HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}
													</a>
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
											<th>Total</th>
											<th>{{ $total }} day/s</th>
										</tr>
									</tfoot>
								</table>
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5"></th>
					<th class="text-primary">Total</th>
					<th class="text-primary">{{ $totalreplacement }} day/s</th>
					<th colspan="2"></th>
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
	// "columnDefs": [
	// 				{ type: 'date', 'targets': [4,5,6] },
	// 				// { type: 'time', 'targets': [6] },
	// 			],
	order: [ 0, 'asc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
