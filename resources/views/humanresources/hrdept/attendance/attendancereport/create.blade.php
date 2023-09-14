@extends('layouts.app')

@section('content')
<?php
use Illuminate\Database\Eloquent\Builder;

?>
<div class="container table-responsive row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<div class="row g-3">
		<h4>Attendance By Staff</h4>
		<p>&nbsp;</p>
		@if($sa)
			@foreach($sa as $v)
				<?php
				$ha = \App\Models\HumanResources\HRAttendance::where('staff_id', $v->staff_id)
						->where(function (Builder $query) use ($request){
							$query->whereDate('attend_date', '>=', $request->from)
							->whereDate('attend_date', '<=', $request->to);
						})
						->get();
				?>
				<h5>{{ \App\Models\Staff::where('id', $v->staff_id)->first()->name }} Attendance</h5>
				<table id="attendancestaff" class="table table-hover table-sm align-middle" style="font-size:12px">
					<thead>
						<tr>
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
					@foreach($ha as $v1)
						<tr>
							<td>{{$v1->daytype_id}}</td>
							<td>{{$v1->attendance_type_id}}</td>
							<td>{{$v1->attend_date}}</td>
							<td>{{$v1->attend_date}}</td>
							<td>{{$v1->in}}</td>
							<td>{{$v1->break}}</td>
							<td>{{$v1->resume}}</td>
							<td>{{$v1->out}}</td>
							<td>{{$v1->time_work_hour}}</td>
							<td>{{$v1->time_work_hour}}</td>
							<td>{{$v1->remarks}}</td>
							<td>{{$v1->exception}}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@endforeach
		@else
		@endif
	</div>




</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// datepicker
$('#from').datetimepicker({
	icons: {
		time: "fas fas-regular fa-clock fa-beat",
		date: "fas fas-regular fa-calendar fa-beat",
		up: "fa-regular fa-circle-up fa-beat",
		down: "fa-regular fa-circle-down fa-beat",
		previous: 'fas fas-regular fa-arrow-left fa-beat',
		next: 'fas fas-regular fa-arrow-right fa-beat',
		today: 'fas fas-regular fa-calenday-day fa-beat',
		clear: 'fas fas-regular fa-broom-wide fa-beat',
		close: 'fas fas-regular fa-rectangle-xmark fa-beat'
	},
	format: 'YYYY-MM-DD',
	useCurrent: true,
})
.on('dp.change dp.update', function(e) {

});

$('#to').datetimepicker({
	icons: {
		time: "fas fas-regular fa-clock fa-beat",
		date: "fas fas-regular fa-calendar fa-beat",
		up: "fa-regular fa-circle-up fa-beat",
		down: "fa-regular fa-circle-down fa-beat",
		previous: 'fas fas-regular fa-arrow-left fa-beat',
		next: 'fas fas-regular fa-arrow-right fa-beat',
		today: 'fas fas-regular fa-calenday-day fa-beat',
		clear: 'fas fas-regular fa-broom-wide fa-beat',
		close: 'fas fas-regular fa-rectangle-xmark fa-beat'
	},
	format: 'YYYY-MM-DD',
	useCurrent: true,
})
.on('dp.change dp.update', function(e) {

});

/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
// $(document).ready(function(){
// 	$('[data-bs-toggle="tooltip"]').tooltip();
// });

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#attendancestaff').DataTable({
	"columnDefs": [
					{ type: 'date', 'targets': [5] },
					{ type: 'time', 'targets': [6] },
					{ type: 'time', 'targets': [7] },
					{ type: 'time', 'targets': [8] },
					{ type: 'time', 'targets': [9] },
				],
	"lengthMenu": [ [-1], ["All"] ],
	"order": [[0, "asc" ]],	// sorting the 6th column descending
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
@endsection
