@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<h2>Add Customer Order</h2>
		{{ Form::open(['route' => ['sales.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
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
						@foreach(\App\Models\Sales\OptSalesType::all() as $key)
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
					<label class="form-check-label col-sm-4 " for="specReq">
						Special Request
						<input type="checkbox" name="special_request" class="form-check-input ml-1" value="1" id="specReq">
					</label>
					<div class="col-sm-8" id="wraptextarea">
					</div>
				</div>

			</div>
			<div class="col-sm-6">

				<div class="form-group row m-2 {{ $errors->has('po_number') ? 'has-error' : '' }}">
					{{ Form::label( 'pon', 'PO Number : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						{{ Form::text('po_number', @$value, ['class' => 'form-control form-control-sm', 'id' => 'pon', 'placeholder' => 'PO Number', 'autocomplete' => 'off']) }}
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('delivery_at') ? 'has-error' : '' }}">
					{{ Form::label( 'delivery', 'Estimation Delivery Date : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8" style="position: relative;">
						{{ Form::text('delivery_at', @$value, ['class' => 'form-control form-control-sm', 'id' => 'delivery', 'placeholder' => 'Estimation Delivery Date', 'autocomplete' => 'off']) }}
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('urgency') ? 'has-error' : '' }}">
					{{ Form::label( 'urgency', 'Mark As Urgent : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						<div class="form-check">
							<input type="checkbox" name="urgency" class="form-check-input" value="1" id="urgency1">
							<label class="form-check-label col-sm-4 " for="urgency1">
								Yes
							</label>
						</div>
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('po_number') ? 'has-error' : '' }}">
					{{ Form::label( 'pon', 'Delivery Instruction : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						@foreach(\App\Models\Sales\OptSalesDeliveryType::all() as $key)
							<div class="form-check form-check-inline m-1">
								<label class="form-check-label" for="dbdid{{ $key->id }}">
									<input class="form-check-input m-1" type="checkbox" name="sales_delivery_id[]" id="dbdid{{ $key->id }}" value="{{ $key->id }}">
									{{ $key->delivery_type }}
								</label>
							</div>
						@endforeach
						{{ Form::textarea('special_delivery_instruction', @$value, ['class' => 'form-control form-control-sm m-1', 'id' => 'sdev', 'placeholder' => 'Special Delivery Instruction', 'autocomplete' => 'off']) }}
					</div>
				</div>

			</div>

			<h5>Job Description &nbsp; <button type="button" class="btn btn-sm btn-outline-secondary jdesc_add"><i class="fa-thin fa-list-check"></i>Add Job Description</button> </h5>
			<div class="col-sm-12">
				<div class="row jdesc_wrap">
					<div class="row m-1 p-1 jdesc_row">




						<div class="col-sm-12 row border border-primary">
							<div class="col-auto m-1 p-1 border border-primary ">
								<button type="button" class="btn btn-sm btn-outline-secondary jdesc_remove">
									<i class="fas fa-trash" aria-hidden="true"></i>
								</button>
							</div>
							<div class="col m-1 p-1  border border-primary form-group {{ $errors->has('jobdesc.*.job_description') ? 'has-error' : '' }}">
								<textarea name="jobdesc[1][job_description]" id="jdi_1" class="form-control form-control-sm" placeholder="Job Description"></textarea>
							</div>
							<div class="col-auto m-1 p-1  border border-primary row form-group {{ $errors->has('jobdesc.*.quantity') ? 'has-error' : '' }} {{ $errors->has('jobdesc.*.uom_id') ? 'has-error' : '' }}">
								<input type="text" name="jobdesc[1][quantity]" id="jdq_1" class="col-3 form-control form-control-sm m-1" placeholder="Quantity">
								<select name="jobdesc[1][uom_id]" id="jdu_1" class="col-8 form-select form-select-sm m-1" placeholder="UOM"></select>
							</div>
							<div class="col-auto m-1 p-1  border border-primary form-group {{ $errors->has('jobdesc.*.relationship_id') ? 'has-error' : '' }}">
								<?php $a = 0; ?>
								@foreach(\App\Models\Sales\OptSalesGetItem::all() as $key)
									<div class="form-check">
										<input type="checkbox" name="jobdesc[1][jd_get_item][]" class="form-check-input" value="{{ $key->id }}" id="jdescitem_{{ $key->id }}{{ $a }}">
										<label class="form-check-label" for="jdescitem_{{ $key->id }}{{ $a }}">{{ $key->get_item }}</label>
									</div>
									<?php $a++ ?>
								@endforeach
							</div>
						</div>
						<div class="col-sm-12 border border-primary">
							<select name="jobdesc[1][machine_id]" id="jobdescmach_1" class=" form-select form-select-sm m-1" placeholder="Machine"></select>
							<select name="jobdesc[1][machine_accessories_id]" id="jobdescmachacc_1" class=" form-select form-select-sm m-1" placeholder="Machine Accessories"></select>
						</div>
						<div class="col-sm-12 border border-primary">
							<div class="col m-1 p-1  form-group {{ $errors->has('jobdesc.*.remarks') ? 'has-error' : '' }}">
								<textarea name="jobdesc[1][remarks]" id="jdr_1" class="form-control form-control-sm" placeholder="Remarks"></textarea>
							</div>
						</div>








					</div>
				</div>
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
$('#nam, #delivery').datetimepicker({
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

/////////////////////////////////////////////////////////////////////////////////////////
// special request description
$('#specReq').change(function() {
	if(this.checked == true) {
		if ($('#sreq').length == 0) {
			$('#wraptextarea').append(
				'{{ Form::textarea('special_request', @$value, ['class' => 'form-control form-control-sm', 'id' => 'sreq', 'placeholder' => 'Special Request Remarks']) }}'
			);
		}
	} else {
		$('#sreq').remove();
	}
});

/////////////////////////////////////////////////////////////////////////////////////////
// select2
$('#jdu_1').select2({
	placeholder: 'UOM',
	// theme: 'bootstrap5',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('uom.uom') }}',
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

/////////////////////////////////////////////////////////////////////////////////////////
// select2
$('#jobdescmach_1').select2({
	placeholder: 'Machine',
	// theme: 'bootstrap5',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('machine.machine') }}',
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

$('#jobdescmachacc_1').select2({
	placeholder: 'Machine Accessories',
	// theme: 'bootstrap5',
	allowClear: true,
	closeOnSelect: true,
	ajax: {
		url: '{{ route('machineaccessories.machineaccessories') }}',
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

/////////////////////////////////////////////////////////////////////////////////////////
// select chained
$('#jobdescmachacc_1').chainedTo();


/////////////////////////////////////////////////////////////////////////////////////////
// add item
var crb_max_fields = 500;						//maximum input boxes allowed
var crb_add_buttons = $(".jdesc_add");
var crb_wrappers = $(".jdesc_wrap");

var xcrb = 1;
$(crb_add_buttons).click(function(){
	// e.preventDefault();

	//max input box allowed
	if(xcrb < crb_max_fields){
		xcrb++;
		crb_wrappers.append(
			'<div class="row m-1 p-1 jdesc_row">' +
				'<div class="col-auto m-1 p-1 ">' +
					'<button type="button" class="btn btn-sm btn-outline-secondary jdesc_remove">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col m-1 p-1  form-group {{ $errors->has('jobdesc.*.job_description') ? 'has-error' : '' }}">' +
					'<textarea name="jobdesc[' + xcrb + '][job_description]" id="jdi_' + xcrb + '" class="form-control form-control-sm" placeholder="Job Description"></textarea>' +
				'</div>' +
				'<div class="col-auto m-1 p-1  row form-group {{ $errors->has('jobdesc.*.quantity') ? 'has-error' : '' }} {{ $errors->has('jobdesc.*.uom_id') ? 'has-error' : '' }}">' +
					'<input type="text" name="jobdesc[' + xcrb + '][quantity]" id="jdq_' + xcrb + '" class="col-3 form-control form-control-sm m-1" placeholder="Quantity">' +
					'<select name="jobdesc[' + xcrb + '][uom_id]" id="jdu_' + xcrb + '" class="col-8 form-select form-select-sm m-1" placeholder="UOM"></select>' +
				'</div>' +
				'<div class="col-auto m-1 p-1  form-group {{ $errors->has('jobdesc.*.relationship_id') ? 'has-error' : '' }}">' +
					<?php $a = 0; ?>
					@foreach(\App\Models\Sales\OptSalesGetItem::all() as $key)
						'<div class="form-check">' +
							'<input type="checkbox" name="jobdesc[' + xcrb + '][jd_get_item][]" class="form-check-input" value="{{ $key->id }}" id="jdescitem_' + xcrb + '{{ $key->id }}{{ $a }}">' +
							'<label class="form-check-label" for="jdescitem_' + xcrb + '{{ $key->id }}{{ $a }}">{{ $key->get_item }}</label>' +
						'</div>' +
						<?php $a++ ?>
					@endforeach
				'</div>' +
				'<div class="col m-1 p-1  form-group {{ $errors->has('jobdesc.*.remarks') ? 'has-error' : '' }}">' +
					'<textarea name="jobdesc[' + xcrb + '][remarks]" id="jdr_' + xcrb + '" class="form-control form-control-sm" placeholder="Remarks"></textarea>' +
				'</div>' +
			'</div>'
		);

		$('#jdu_' + xcrb ).select2({
			placeholder: 'Please Select',
			// width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('uom.uom') }}',
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

		//bootstrap validate
		$('#form').bootstrapValidator('addField',	$('.crossbackup_row')	.find('[name="crossbackup['+ xcrb +'][backup_staff_id]"]'));
	}
})

$(crb_wrappers).on("click",".jdesc_remove", function(e){
	//user click on remove text
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="crossbackup[' + xcrb + '][backup_staff_id]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	xcrb--;
})


/////////////////////////////////////////////////////////////////////////////////////////
@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection


