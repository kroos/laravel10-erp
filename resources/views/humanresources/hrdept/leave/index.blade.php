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

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Session;

use \App\Helpers\UnavailableDateTime;

$upleave = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
			->whereDate('date_time_start', '>', now())
			// ->where(function (Builder $query) {
			// 	$query->whereDate('date_time_start', '<=', $s->attend_date)
			// 	->whereDate('date_time_end', '>=', $s->attend_date);
			// })
			->first();
?>


<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Leave</h4>
	<p>&nbsp;</p>
	<h5>Upcoming Leave</h5>
	<table>
		<thead>
			<tr>
				<th>No</th>
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






			<tr>
				<td></td>
			</tr>

		</tbody>
	</table>


















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
// $.fn.dataTable.moment( 'h:mm a' );
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
