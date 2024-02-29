@extends('layouts.app')

@push('styles')
	@livewireStyles
@endpush

@push('scripts')
	@livewireScripts
@endpush

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h2>Staff with Conditional Incentive</h2>

	<div class="table-responsive">
		<table class="table table-sm table-hover">
			<thead>
				<tr>
					<th>Staff</th>
					<th>Conditional Incentive</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($incentivestaffs as $incentivestaff)
				<tr>
					<td>{{ $incentivestaff->name }}</td>
					<td>
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Description</th>
									<th>Incentive Deduction</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($incentivestaff->belongstomanycicategoryitem()?->get() as $k => $v)
									<tr>
										<td>{{ $k + 1 }}</td>
										<td>{!! nl2br($v->description) !!}</td>
										<td>MYR {{ $v->point }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
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
//tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#category').DataTable({
	"paging": true,
	"lengthMenu": [ [25,50,100,-1], [25,50,100,"All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
	],
	"order": [ 2, 'desc' ], // sorting the column descending
	responsive: true
}).on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_discipline', function(e){
	var ackID = $(this).data('id');
	var ackSoftcopy = $(this).data('softcopy');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackSoftcopy, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable){
	swal.fire({
		title: 'Delete Discipline',
		text: 'Are you sure to delete this discipline?',
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
					url: '{{ url('discipline') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						softcopy: ackSoftcopy,
						table: ackTable,
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
// //auto refresh right after clicking OK button
// $(document).on('click', '.swal2-confirm', function(e){
// 	window.location.reload(true);
// });
@endsection


@section('nonjquery')

@endsection
