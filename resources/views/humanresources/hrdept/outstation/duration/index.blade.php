@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Staff Outstation Duration</h4>
	<div id="calendar" class="col-sm-12 m-3"></div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////
// fullcalendar
var calendarEl = document.getElementById('calendar');
var calendar = new FullCalendar.Calendar(calendarEl, {
	aspectRatio: 1.0,
	height: 2000,
	// plugins: [multiMonthPlugin],
	initialView: 'multiMonthYear',
	// initialView: 'dayGridMonth',
	headerToolbar: {
		left: 'prev,next today',
		center: 'title',
		right: 'multiMonthYear,dayGridMonth,timeGridWeek'
	},
	weekNumbers: true,
	themeSystem: 'bootstrap',
	events: {
		url: '{{ route('staffoutstationduration') }}',
		method: 'GET',
		extraParams: {
			_token: '{!! csrf_token() !!}',
			staff_id: '117',
		},
	},
	// failure: function() {
	// 	alert('There was an error while fetching leaves!');
	// },
	eventDidMount: function(info) {
		$(info.el).tooltip({
			title: info.event.extendedProps.description,
			placement: 'top',
			trigger: 'hover',
			container: 'body'
		});
	},
	eventTimeFormat: { // like '14:30:00'
		hour: '2-digit',
		minute: '2-digit',
		second: '2-digit',
		hour12: true
	}
});
calendar.render();
@endsection
