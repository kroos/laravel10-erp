@extends('layouts.app')

@section('content')
<?php
use App\Models\Login;
use App\Models\Staff;
use App\Models\HumanResources\HROutstation;
use \Carbon\Carbon;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

$now = now();
$startdatenowyear = $now->copy()->startOfYear();
$enddatenowyear = $now->copy()->endOfYear();
$nowyear = $startdatenowyear->copy()->format('Y');

$enddatelastyear = $startdatenowyear->copy()->subDay();
$startdatelastyear = $enddatelastyear->copy()->startOfYear();
$lastyear = $startdatelastyear->copy()->format('Y');

// dd($startdatenowyear, $enddatenowyear, $startdatelastyear, $enddatelastyear);
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Outstation List&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('outstation.create') }}"><i class="fa-solid fa-person-digging fa-beat"></i> Add Outstation</a></h4>
	<div class="table-responsive m-3">
		<table class="table table-hover table-sm" id="nowoutstation" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center" colspan="8">{{ $nowyear }}</th>
				</tr>
				<tr>
					<th>ID Staff</th>
					<th>Staff</th>
					<th>Location</th>
					<th>From</th>
					<th>To</th>
					<!-- <th>Duration</th> -->
					<th>Remarks</th>
					<th>#</th>
				</tr>
			</thead>
			<tbody>
				@foreach(HROutstation::where('active', 1)->whereYear('date_from', $nowyear)->orderBy('date_to', 'DESC')->get() as $key => $outstation)
					<tr>
						<td>{{ Login::where([['active', 1], ['staff_id', $outstation->staff_id]])->first()?->username }}</td>
						<td>{{ Staff::find($outstation->staff_id)->name }}</td>
						<td>{{ $outstation->belongstocustomer?->customer }}</td>
						<td>{{ Carbon::parse($outstation->date_from)->format('D, j M Y') }}</td>
						<td>{{ Carbon::parse($outstation->date_to)->format('D, j M Y') }}</td>
						<!-- <td>{{ Carbon::parse($outstation->date_from)->toPeriod($outstation->date_to, 1, 'day')->count() }} day/s</td> -->
						<td {!! ($outstation->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$outstation->remarks.'"':null !!}>
							{{ Str::limit($outstation->remarks, 7, ' >') }}
						</td>
						<td>
							<a href="{{ route('outstation.edit', $outstation->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							<button type="button" id="out" class="btn btn-sm btn-outline-secondary text-danger delete_button" data-id="{{ $outstation->id }}"><i class="fa-regular fa-trash-can"></i></button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="table-responsive m-3">
		<table class="table table-hover table-sm" id="lastoutstation" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center" colspan="8">{{ $lastyear }}</th>
				</tr>
				<tr>
					<th>ID Staff</th>
					<th>Staff</th>
					<th>Location</th>
					<th>From</th>
					<th>To</th>
					<!-- <th>Duration</th> -->
					<th>Remarks</th>
					<th>#</th>
				</tr>
			</thead>
			<tbody>
				@foreach(HROutstation::where('active', 1)->whereYear('date_from', $lastyear)->orderBy('date_to', 'DESC')->get() as $key => $outstation)
					<tr>
						<td>{{ Login::where([['active', 1], ['staff_id', $outstation->staff_id]])->first()?->username }}</td>
						<td>{{ Staff::find($outstation->staff_id)->name }}</td>
						<td>{{ $outstation->belongstocustomer?->customer }}</td>
						<td>{{ Carbon::parse($outstation->date_from)->format('D, j M Y') }}</td>
						<td>{{ Carbon::parse($outstation->date_to)->format('D, j M Y') }}</td>
						<!-- <td>{{ Carbon::parse($outstation->date_from)->toPeriod($outstation->date_to, 1, 'day')->count() }} day/s</td> -->
						<td {!! ($outstation->remarks)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$outstation->remarks.'"':null !!}>
							{{ Str::limit($outstation->remarks, 7, ' >') }}
						</td>
						<td>
							<a href="{{ route('outstation.edit', $outstation->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							<button type="button" id="out" class="btn btn-sm btn-outline-secondary text-danger delete_button" data-id="{{ $outstation->id }}"><i class="fa-regular fa-trash-can"></i></button>
						</td>
					</tr>
				@endforeach
			</tbody>
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
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#nowoutstation,#lastoutstation').DataTable({
	"lengthMenu": [ [100, 250, 500, -1], [100, 250, 500, "All"] ],
	"columnDefs": [ { type: 'date', 'targets': [3, 4] } ],
	"order": [[4, "desc"], [3, "desc"]],	// sorting the 5th column descending
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
