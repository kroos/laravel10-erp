@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRLeaveMC;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Medical Certificate Leave Entitlement &nbsp; <button type="button" id="genal" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Generate Medical Certificate Leave For Next Year</button> </h4>
	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach(HRLeaveMC::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Medical Certificate Leave Entitlement ({{ $tp->year }}) for Active Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Medical Certificate Leave</th>
				<th>Medical Certificate Leave Adjustment</th>
				<th>Medical Certificate Leave Utilize</th>
				<th>Medical Certificate Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRLeaveMC::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
			@if($t->belongstostaff->active == 1)
				<tr>
					<td>{{ $t->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
					<td>{{ $t->belongstostaff->name }}</td>
					<td>{{ $t->mc_leave }} day/s</td>
					<td>{{ $t->mc_leave_adjustment }} day/s</td>
					<td>{{ $t->mc_leave_utilize }} day/s</td>
					<td>{{ $t->mc_leave_balance }} day/s</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('mcleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
				</tr>
			@endif
		@endforeach
		</tbody>
	@endforeach
	</table>
	<p>&nbsp;</p>
	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach(HRLeaveMC::groupBy('year')->select('year')->orderBy('year', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="8">Medical Certificate Leave Entitlement ({{ $tp->year }}) For Inactive Staff</th>
			</tr>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Medical Certificate Leave</th>
				<th>Medical Certificate Leave Adjustment</th>
				<th>Medical Certificate Leave Utilize</th>
				<th>Medical Certificate Leave Balance</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRLeaveMC::where('year', $tp->year)->orderBy('year', 'DESC')->get() as $t)
			@if($t->belongstostaff->active <> 1)
				<tr>
					<td>{{ $t->belongstostaff->hasmanylogin()->first()?->username }}</td>
					<td>{{ $t->belongstostaff->name }}</td>
					<td>{{ $t->mc_leave }} day/s</td>
					<td>{{ $t->mc_leave_adjustment }} day/s</td>
					<td>{{ $t->mc_leave_utilize }} day/s</td>
					<td>{{ $t->mc_leave_balance }} day/s</td>
					<td>{{ $t->remarks }}</td>
					<td><a class="btn btn-sm btn-outline-secondary" href="{{ route('mcleave.edit', $t->id) }}"><i class="far fa-edit"></i></a></td>
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
// ajax post delete row
$(document).on('click', '#genal', function(e){
	// var outId = $(this).data('id');
	SwalGenerate();
	e.preventDefault();
});

function SwalGenerate(){
	swal.fire({
		title: 'Are you sure?',
		text: "System will generate Medical Certificate Leave Entitlement for each of active staff",
		type: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, generate it!',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ route('generatemcleave') }}',
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
			swal.fire('Cancelled', 'System did not generate Medical Certificate Leave Entitlements.', 'info')
		}
	});
}

/////////////////////////////////////////////////////////////////////////////////////////
@endsection
