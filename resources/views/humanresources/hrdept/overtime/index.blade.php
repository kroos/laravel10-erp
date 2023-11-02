@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;
use \Carbon\Carbon;
?>
<div class="container row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h2>Staffs Overtime&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('overtime.create') }}"><i class="fa-solid fa-person-circle-plus fa-beat"></i> Add Staff Overtime</a></h2>
	<div class="d-flex justify-content-center">
		{!! $sa->links() !!} <!-- check this for this type of pagination -->
	</div>
	<div class="table-responsive">
		<table id="overtime" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th rowspan="2">ID</th>
					<th rowspan="2">Name</th>
					<th rowspan="2">Date</th>
					<th colspan="2" rowspan="1">Overtime</th>
					<th rowspan="2">Assign By</th>
					<th rowspan="2">#</th>
				</tr>
				<tr>
					<th>Start Time</th>
					<th>End Time</th>
				</tr>
			</thead>
			<tbody>
				@foreach($overtime as $key)
					<tr>
						<td>{{ $key->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
						<td>{{ $key->belongstostaff?->name }}</td>
						<td>{{ Carbon::parse($key->ot_date)->format('j M Y') }}</td>
						<td>{{ Carbon::parse($key->belongstoovertimerange?->start)->format('g:i a') }}</td>
						<td>{{ Carbon::parse($key->belongstoovertimerange?->end)->format('g:i a') }}</td>
						<td>{{ $key->belongstoassignstaff?->name }}</td>
						<td>
							<a href="{{ route('overtime.edit', $key->id) }}" class="btn btn-sm btn-outline-secondary">
								<i class="bi bi-pencil-square" style="font-size: 15px;"></i>
							</a>
							<button type="button" class="btn btn-sm btn-outline-secondary delete_overtime" data-id="{{ $key->id }}" >
								<i class="fa-regular fa-trash-can"></i>
							</button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="d-flex justify-content-center">
		{!! $sa->links() !!} <!-- check this for this type of pagination -->
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
$('#overtime').DataTable({
	// "lengthMenu": [ [10,25,50,100,150,200,-1], [10,25,50,100,150,200,"All"] ],
	"lengthMenu": [ [-1], ["All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
					{ type: 'time', 'targets': [4] },
				],
	"order": [[2, "DESC" ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_overtime', function(e){
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable){
	swal.fire({
		title: 'Delete Overtime',
		text: 'Are you sure to delete this overtime?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('overtime') }}' + '/' + ackID,
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
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
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

@endsection

@section('nonjquery')
@endsection
