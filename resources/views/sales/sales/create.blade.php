@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<h2>Add Customer Order</h2>
		{{ Form::open(['route' => ['staff.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
		<div class="col-sm-12 row">
			<div class="col-sm-6">

				<div class="form-group row m-2 {{ $errors->has('date_order') ? 'has-error' : '' }}">
					{{ Form::label( 'nam', 'Date : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8" style="position: relative;">
						{{ Form::text('date_order', @$value, ['class' => 'form-control form-control-sm', 'id' => 'nam', 'placeholder' => 'Date', 'autocomplete' => 'off']) }}
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('customer_id') ? 'has-error' : '' }}">
					{{ Form::label( 'cust', 'Customer : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						<select name="customer_id" id="cust" class="form-select form-select-sm" placeholder="Please choose"></select>
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('deliveryby_id') ? 'has-error' : '' }}">
					{{ Form::label( 'otype', 'Type Of Order : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						@foreach(\App\Models\Sales\SalesType::all() as $key)
							<div class="form-check form-check-inline">
								<label class="form-check-label" for="db{{ $key->id }}">
									<input class="form-check-input m-1" type="radio" name="deliveryby_id" id="db{{ $key->id }}" value="{{ $key->id }}">
									{{ $key->order_type }}
								</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="form-group form-check row m-2 {{ $errors->has('date_order') ? 'has-error' : '' }}">
					<label class="form-check-label" for="specReq">
						Special Request
						<input type="checkbox" name="special_request" class="form-check-input ml-1" value="1" id="specReq">
					</label>
					<div class="col-sm-8" id="wraptextarea">

					</div>
				</div>





			</div>
			<div class="col-sm-6">

			</div>
		</div>
		<div class="d-flex justify-content-center m-3">
			{!! Form::submit('Add Order', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
		</div>
		{{ Form::close() }}
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
// date
$('#nam').datetimepicker({
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
	format:'YYYY-MM-DD',
	// useCurrent: false,
});

/////////////////////////////////////////////////////////////////////////////////////////
// customer
$('#cust').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('customer.customer') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
				search: params.term,
			}
			return query;
		}
	},
});

@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection


