@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h4>Leave</h4>
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

@section('fullcalendar')
/////////////////////////////////////////////////////////////////////////////////////////
// fullcalendar cant use jquery
document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');
	var calendar = new FullCalendar.Calendar(calendarEl, {
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
});

/////////////////////////////////////////////////////////////////////////////////////////

@endsection
