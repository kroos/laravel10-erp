@extends('layouts.app')

@section('content')
<?php
use Illuminate\Database\Eloquent\Builder;
?>

<style>
	.scrollable-div {
		/* Set the width height as needed */
/*		width: 100%;*/
		height: 400px;
		background-color: blanchedalmond;
		/* Add scrollbars when content overflows */
		overflow: auto;
	}

	p {
		margin-top: 4px;
		margin-bottom: 4px;
	}
</style>
<div class="container table-responsive row align-items-start justify-content-center">
@include('humanresources.hrdept.navhr')
	<h4>Attendance Report</h4>

	{{ Form::open(['route' => 'attendancereport.store', 'method' => 'GET',  'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
	<div class="row g-3 mb-3">
		<div class="col-auto" style="position:relative;">
			<input type="text" name="from" class="form-control form-control-sm" id="from" value="" placeholder="Date From">
		</div>
		<div class="col-auto" style="position:relative;">
			<input type="text" name="to" class="form-control form-control-sm" id="to" value="" placeholder="Date To">
		</div>
		<div class="col-auto">
			<input type="submit" class="form-control form-control-sm btn btn-sm btn-outline-secondary" id="to" value="Submit">
		</div>
	</div>
	<div class="g-3 mb-3 py-3 scrollable-div col-sm 5 wrap_checkbox">
	</div>
	{{ Form::close() }}
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
	maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
	// useCurrent: false,
})
.on('dp.change', function(e) {
	$('#to').datetimepicker('minDate', $('#from').val());
	if($('.remove_checkbox').length !== 0) {		// remove any checkbox if there is any to put a new 1
		$('.remove_checkbox').remove();
	}
	if($('#to').val().length !== 0) {			// to ensure the to must be filled
		var a = $.ajax({
							url: "{{ route('staffattendancelist') }}",
							type: "POST",
							data: {
									from: $('#from').val(),
									to: $('#to').val(),
									_token: '{!! csrf_token() !!}',
								},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

		var b = $.ajax({
							url: "{{ route('branchattendancelist') }}",
							type: "POST",
							data: {
									_token: '{!! csrf_token() !!}',
								},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

		// convert data10 into json
		var obj = $.parseJSON( a );
		var brc = $.parseJSON( b );
		var i = 1;
		if($('.wrap_checkbox').children().length === 0) {
			$('.wrap_checkbox').append(
											'<div class="form-check form-check-inline mb-1 g-3 remove_checkbox">' +
												'<input class="form-check-input" type="checkbox" value="" id="checkAll">' +
												'<label class="form-check-label" for="checkAll">Name</label>' +
											'</div>'
			);
			$.each( brc, function() {
				$('.wrap_checkbox').append(
											'<div class="form-check form-check-inline mb-1 g-3 remove_checkbox">' +
												'<input class="form-check-input" type="checkbox" value="" id="branch_' + this.id + '">' +
												'<label class="form-check-label" for="branch_' + this.id + '">' + this.location + '</label>' +
											'</div>'
				);
				$("#branch_' + this.id + '").change(function () {
					$("input:checkbox").prop('checked', this.checked);
				});
			});
			$.each( obj, function() {
				$('.wrap_checkbox').append(
											'<div class="form-check mb-1 g-3 remove_checkbox" style="vertical-align: middle;">' +
												'<input class="form-check-input" name="staff_id" type="checkbox" value="' + this.id + '" id="staff_' + i + '" >' +
												'<label class="form-check-label" for="staff_' + i + '">' + 
												this.username + 
												' - ' +
												this.name +
												'&nbsp;&nbsp;&nbsp;[' +
												this.department +
												']' + 
												'</label>' +
											'</div>'
				);
				i++
			});
			$("#checkAll").change(function () {
				$("input:checkbox").prop('checked', this.checked);
			});
			@foreach(App\Models\HumanResources\OptBranch::all() as $br)
				$("#branch_{{ $br->id }}").change(function () {
					$("input.{{ $br->id }}[type=checkbox]").prop('checked', this.checked);
				});
			@endforeach
		}
	}
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
	maxDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
	// useCurrent: false,
})
.on('dp.change', function(e) {
	$('#from').datetimepicker('maxDate', $('#to').val());
	if($('.remove_checkbox').length !== 0) {		// remove any checkbox if there is any to put a new 1
		$('.remove_checkbox').remove();
	}
	if($('#from').val().length !== 0) {				// to ensure the from must be filled
		var a = $.ajax({
							url: "{{ route('staffattendancelist') }}",
							type: "POST",
							data: {
									from: $('#from').val(),
									to: $('#to').val(),
									_token: '{!! csrf_token() !!}',
								},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

		var b = $.ajax({
							url: "{{ route('branchattendancelist') }}",
							type: "POST",
							data: {
									_token: '{!! csrf_token() !!}',
								},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

		// convert data10 into json
		var obj = $.parseJSON( a );
		var brc = $.parseJSON( b );
		var i = 1;
		if($('.wrap_checkbox').children().length === 0) {
			$('.wrap_checkbox').append(
											'<div class="form-check form-check-inline mb-1 g-3 remove_checkbox">' +
												'<input class="form-check-input" type="checkbox" value="" id="checkAll">' +
												'<label class="form-check-label" for="checkAll">Name</label>' +
											'</div>'
			);
			$.each( brc, function() {
				$('.wrap_checkbox').append(
											'<div class="form-check form-check-inline mb-1 g-3 remove_checkbox">' +
												'<input class="form-check-input" type="checkbox" value="" id="branch_' + this.id + '">' +
												'<label class="form-check-label" for="branch_' + this.id + '">' + this.location + '</label>' +
											'</div>'
				);
			});
			$.each( obj, function() {
				$('.wrap_checkbox').append(
							'<div class="form-check mb-1 g-3 remove_checkbox" style="vertical-align: middle;">' +
								'<input class="form-check-input ' + this.branch + '" name="staff_id[]" type="checkbox" value="' + this.id + '" id="staff_' + i + '">' +
								'<label class="form-check-label" for="staff_' + i + '">' + 
									this.username + 
									' - ' +
									this.name +
									'&nbsp;&nbsp;&nbsp;[' +
									this.department +
									']' +
									'</label>' +
							'</div>'
				);
				i++
			});
			$("#checkAll").change(function () {
				$("input:checkbox").prop('checked', this.checked);
			});
			@foreach(App\Models\HumanResources\OptBranch::all() as $br)
				$("#branch_{{ $br->id }}").change(function () {
					$("input.{{ $br->id }}[type=checkbox]").prop('checked', this.checked);
				});
			@endforeach
		}
	}
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
