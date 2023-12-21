@extends('layouts.app')

@section('content')
<?php
use App\Models\Login;
use App\Models\Staff;
use \Carbon\Carbon;
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Auto Attendance Remarks
		&nbsp;
		<a href="{{ route('attendanceremark.create') }}" class="btn btn-sm btn-outline-secondary">
			<i class="fa-regular fa-note-sticky fa-beat"></i> Add Remarks
		</a>
	</h4>
	<div class="col-sm-12 row">
		@if($attendanceremark)
		<div class="table-responsive">
			<table class="table table-sm table-hover" id="attendance" style="font-size:12px">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>From</th>
						<th>To</th>
						<th>Remarks Attendance</th>
						<th>HR Remarks Attendance</th>
						<th>Remarks</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($attendanceremark as $v)
					<tr>
						<td>{{ Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username }}</td>
						<td>{{ Staff::find($v->staff_id)->name }}</td>
						<td>{{ ($v->date_from)?Carbon::parse($v->date_from)->format('j M Y'):NULL }}</td>
						<td>{{ ($v->date_to)?Carbon::parse($v->date_to)->format('j M Y'):NULL }}</td>
						<td {!! ($v->attendance_remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$v->attendance_remarks.'"':NULL !!}>
							{{ Str::limit($v->attendance_remarks, 7, ' >') }}
						</td >
						<td {!! ($v->hr_attendance_remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$v->hr_attendance_remarks.'"':NULL !!}>
							{{ Str::limit($v->hr_attendance_remarks, 7, ' >') }}
						</td>
						<td {!! ($v->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$v->remarks.'"':NULL !!}>
							{{ Str::limit($v->remarks, 7, ' >') }}
						</td>
						<td>
							<a href="{{ route('attendanceremark.edit', $v->id) }}" class="btn btn-sm btn-outline-secondary">
								<i class="fa-regular fa-pen-to-square fa-beat"></i>
							</a>
							&nbsp;
							<button class="btn btn-sm btn-outline-secondary text-danger delete" data-id="{{ $v->id }}">
								<i class="fa-regular fa-calendar-xmark fa-beat"></i>
							</button>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		@endif
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#date').datetimepicker({
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
});


/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#attendance').DataTable({
	// "paging": false,
	"lengthMenu": [ [50, 100, 200, -1], ["50", "100", "200", "All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2, 3] },
				],
	"order": [[ 2, 'desc' ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete', function(e){
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID){
	swal.fire({
		title: 'Delete Overtime',
		text: 'Are you sure to delete this Outstation Attendance?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				console.log(resolve);
				$.ajax({
					url: '{{ url('attendanceremark') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(jqXHR, textStatus, errorThrown){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
					console.log(jqXHR, textStatus, errorThrown);
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	})
};
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
