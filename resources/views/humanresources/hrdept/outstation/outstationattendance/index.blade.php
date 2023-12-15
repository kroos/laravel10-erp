@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HROutstationAttendance;
use \Carbon\Carbon;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

?>
<div class="col-sm-12 row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Outstation Attendance List&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('hroutstationattendance.create') }}"><i class="fa-solid fa-person-digging fa-beat"></i> Add Outstation Attendance</a></h4>

	@if($hroa->count())
	<div class="table-responsive">
		<table id="outstation" class="table table-hover table-sm align-middle" style="font-size:12px">
				<thead>
					<tr>
						<th>ID</th>
						<th>Staff</th>
						<th>Location</th>
						<th>Date</th>
						<th>In</th>
						<th>Detected Region In</th>
						<th>Detected City Out</th>
						<th>Out</th>
						<th>Detected Region Out</th>
						<th>Detected City Out</th>
						<th>Remarks</th>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
				@foreach($hroa as $k => $v)
					<tr>
						<td>{{ Login::where([['staff_id', $v->staff_id], ['active', 1]])->first()?->username }}</td>
						<td>{{ Staff::find($v->staff_id)->name }}</td>
						<td>{{ HROutstation::find($v->outstation_id)->belongstocustomer?->customer }}</td>
						<td>{{ ($v->date_attend)?Carbon::parse($v->date_attend)->format('j M Y'):NULL }}</td>
						<td>{{ ($v->in)?Carbon::parse($v->in)->format('g:i a'):NULL }}</td>
						<td>{{ $v->in_regionName }}</td>
						<td>{{ $v->in_cityName }}</td>
						<td>{{ ($v->out)?Carbon::parse($v->out)->format('g:i a'):NULL }}</td>
						<td>{{ $v->out_regionName }}</td>
						<td>{{ $v->out_cityName }}</td>
						<td {!! ($v->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$v->remarks.'"':NULL !!}>
							{{ Str::limit($v->remarks, 8, ' >') }}
						</td>
						<td>
							<a href="{{ route('hroutstationattendance.edit', $v->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							<button type="button" id="out" class="btn btn-sm btn-outline-secondary text-danger delete_button" data-id="{{ $v->id }}"><i class="fa-regular fa-trash-can"></i></button>
						</td>
					</tr>
				@endforeach
				</tbody>
		</table>
	</div>
	@endif
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
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#outstation').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [3] },
					{ type: 'time', 'targets': [4, 5] }
				],
	"order": [[3, "desc" ]],	// sorting the 5th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// ajax post delete row
$(document).on('click', '.delete_button', function(e){

	var outId = $(this).data('id');
	SwalDelete(outId);
	e.preventDefault();
});

function SwalDelete(outId){
	swal.fire({
		title: 'Are you sure?',
		text: "It will be deleted permanently!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('outstation') }}' + '/' + outId,
					type: 'DELETE',
					data: {
							_token : $('meta[name=csrf-token]').attr('content'),
							id: outId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Deleted!', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
					//$('#delete_product_' + outId).parent().parent().remove();
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your data is safe from delete', 'info')
		}
	});
}



@endsection
