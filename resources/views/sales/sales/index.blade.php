@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Customer Order &nbsp; <a href="{{ route('sales.create') }}" class="btn btn-sm btn-outline-secondary" > <span class="mdi mdi-point-of-sale"></span>Add Order </a></h2>
			<table class="table table-sm table-hover m-3" id="sales">
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>Customer</th>
						<th>Delivery Date</th>
						<th>Special Request</th>
						<th>Urgency</th>
						<th>Send Status</th>
						<th>Approval</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>ID</td>
						<td>Date</td>
						<td>Customer</td>
						<td>Delivery Date</td>
						<td>Special Request</td>
						<td>Urgency</td>
						<td>Send Status</td>
						<td>Approval</td>
					</tr>
				</tbody>
			</table>
		</div>
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
$.fn.dataTable.moment( 'YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#sales').DataTable({
	"lengthMenu": [ [30, 60, 100, -1], [30, 60, 100, "All"] ],
	"columnDefs": [
		{ type: 'date', 'targets': [1,3] },
	],
	"order": [[ 0, 'desc' ]],
	"responsive": true,
	"autoWidth": false,
	fixedHeader: true,
	dom: 'Bfrtip',
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});
});

/////////////////////////////////////////////////////////////////////////////////////////





@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection


