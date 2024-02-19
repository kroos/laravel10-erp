@extends('layouts.app')

@section('content')
<?php
use \Carbon\Carbon;
?>
<div class="col-sm-12 row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Customer Order &nbsp; <a href="{{ route('sale.create') }}" class="btn btn-sm btn-outline-secondary" > <span class="mdi mdi-point-of-sale"></span>Add Order </a></h2>
			<table class="table table-sm table-hover m-3" id="sales" style="font: 12px sans-serif;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>Customer</th>
						<th>Delivery Date</th>
						<th>Special Request</th>
						<th>Urgency</th>
						<th>Send Status</th>
						<th>Approval By</th>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
					@foreach($sales as $sale)
						<tr>
							<td>{{ $sale->belongstosalesby->sales_by.'-'.str_pad( $sale->no, 3, "0", STR_PAD_LEFT ).'/'.$sale->year }}</td>
							<td>{{ Carbon::parse($sale->date_order)->format('j M Y') }}</td>
							<td {!! ($sale->belongstocustomer?->customer)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$sale->belongstocustomer?->customer.'"':NULL !!}>
								{{ Str::limit($sale->belongstocustomer?->customer, 10, ' >') }}
							</td>
							<td>{{ Carbon::parse($sale->delivery_at)->format('j M Y') }}</td>
							<td {!! ($sale->special_request)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.nl2br($sale->special_request).'"':NULL !!}>
								{!! Str::limit(nl2br($sale->special_request), 10, ' >') !!}
							</td>
							<td>{!! ($sale->urgency==1)?'<i class="fa-regular fa-circle-check fa-beat fa-1x"></i>':'<i class="fa-regular fa-circle-xmark fa-beat fa-1x"></i>' !!}</td>
							<td>
								{!! ($sale->confirm==1)?'Send':Null !!}
							</td>
							<td {!! !is_null($sale->approved_by)?'data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.$sale->belongstostaff?->name.'"':NULL !!}>
								{{ Str::words(!is_null($sale->approved_by)?$sale->belongstostaff?->name:NULL, 2, ' >') }}
							</td>
							<td>
								{!!
									!is_null($sale->approved_by)?
									NULL:
									'<a href="'.route('sale.edit', $sale->id).'" class="btn btn-sm btn-outline-secondary">
										<i class="fa-regular fa-pen-to-square fa-beat"></i>
									</a>
									<button class="btn btn-sm btn-outline-secondary" data-id="'.$sale->id.'">
										<i class="fa-solid fa-trash-can fa-beat" style="color: red;"></i>
									</button>'
								!!}
							</td>
						</tr>
					@endforeach
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
	"order": [[ 1, 'desc' ]],
	"responsive": true,
	"autoWidth": false,
	"fixedHeader": true,
	// "dom": 'Bfrtip',
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


