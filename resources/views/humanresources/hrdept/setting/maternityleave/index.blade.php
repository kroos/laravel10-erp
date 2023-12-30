@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRLeaveMaternity;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Maternity Leave Entitlement &nbsp; <button type="button" id="genal" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Generate Maternity Leave For Next Year</button> </h4>
	<table class="table table-hover table-sm" id="mll" style="font-size:12px">
	@foreach(HRLeaveMaternity::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Maternity Leave Entitlement ({{ $tp->year }}) for Active Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Maternity Leave</th>
				<th>Maternity Leave Adjustment</th>
				<th>Maternity Leave Utilize</th>
				<th>Maternity Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRLeaveMaternity::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
			@if($t->belongstostaff->active == 1 && $t->belongstostaff->gender_id == 2)
				<tr>
					<td>{{ $t->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
					<td>{{ $t->belongstostaff->name }}</td>
					<td>{{ $t->maternity_leave }} day/s</td>
					<td>{{ $t->maternity_leave_adjustment }} day/s</td>
					<td>{{ $t->maternity_leave_utilize }} day/s</td>
					<td>{{ $t->maternity_leave_balance }} day/s</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('maternityleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
			@endif
		@endforeach
		</tbody>
	@endforeach
	</table>
	<p>&nbsp;</p>
	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach(HRLeaveMaternity::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Maternity Leave Entitlement ({{ $tp->year }}) For Inactive Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Annual Leave</th>
				<th>Annual Leave Adjustment</th>
				<th>Annual Leave Utilize</th>
				<th>Annual Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRLeaveMaternity::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
			@if($t->belongstostaff->active <> 1 && $t->belongstostaff->gender_id == 2)
				<tr>
					<td>{{ $t->belongstostaff->hasmanylogin()->first()?->username }}</td>
					<td>{{ $t->belongstostaff->name }}</td>
					<td>{{ $t->maternity_leave }} day/s</td>
					<td>{{ $t->maternity_leave_adjustment }} day/s</td>
					<td>{{ $t->maternity_leave_utilize }} day/s</td>
					<td>{{ $t->maternity_leave_balance }} day/s</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('maternityleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
			@endif
		@endforeach
		</tbody>
	@endforeach
	</table>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#mll').DataTable({
	"lengthMenu": [ [-1], ["All"] ],
	// "columnDefs": [
	// 				{ type: 'date', 'targets': [4,5,6] },
	// 				// { type: 'time', 'targets': [6] },
	// 			],
	"order": [ 0, 'asc' ],
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// ajax post delete row
$(document).on('click', '#genal', function(e){
	// var outId = $(this).data('id');
	SwalGenerate();
	e.preventDefault();
});

function SwalGenerate(){
	swal.fire({
		title: 'Are you sure?',
		text: "System will generate Maternity Leave Entitlement for each of female & active staff",
		type: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, generate it!',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ route('generatematernityleave') }}',
					type: 'POST',
					data: {
							_token : $('meta[name=csrf-token]').attr('content'),
							// id: outId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Done!', response.message, response.status)
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
			swal.fire('Cancelled', 'System did not generate Maternity Leave Entitlements.', 'info')
		}
	});
}

/////////////////////////////////////////////////////////////////////////////////////////
@endsection
