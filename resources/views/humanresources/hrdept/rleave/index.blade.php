@extends('layouts.app')

@section('content')
<style>
	.btn-sm-custom {
		padding: 0px;
		background: none;
		border: none;
		font-size: 15px;
		width: 100%;
		height: 100%;
	}
</style>

<div class="container">
	@include('humanresources.hrdept.navhr')
	<h4>Replacement Leave&nbsp; <a class="btn btn-sm btn-outline-secondary" href="{{ route('rleave.create') }}"><i class="fa-solid fa-person-walking-arrow-loop-left fa-beat"></i> Add Replacement Leave</a></h4>
	<div>
		<table id="replacement" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>ID</th>
				</tr>
			</thead>
		</table>
	</div>
</div>
@endsection


@section('js')
$('#replacement').DataTable({
"ajax": "{{ route('rleave.replacement_ajax') }}",
"deferRender": true;
});




/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#replacement-test').DataTable({
	"paging": false,
	"lengthMenu": [ [10,25,50,-1], [10,25,50,"All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
	],
	"order": [ 2, 'desc' ], // sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip()
	});
});

$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})


/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_replacement', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({
		title: 'Delete Replacement Leave',
		text: 'Are you sure to delete this replacement?',
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
					url: '{{ url('rleave') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
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
	});
}
@endsection


@section('nonjquery')

@endsection
