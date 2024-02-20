@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
@include('sales.salesdept.navhr')
	<div class="row justify-content-center">
		<h2>Add Customer Order</h2>

		{{ Form::model($sale, ['route' => ['sale.update', $sale->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
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
						{{ Form::select('customer_id', [], @$value, ['id' => 'cust', 'class' => 'form-select form-select-sm', 'placeholder' => 'Please choose']) }}
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('deliveryby_id') ? 'has-error' : '' }}">
					{{ Form::label( 'otype', 'Order Type : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						@foreach(\App\Models\Sales\OptSalesType::all() as $key)
							<div class="form-check form-check-inline">
								<label class="form-check-label" for="db{{ $key->id }}">
									<input class="form-check-input m-1" type="radio" name="sales_type_id" id="db{{ $key->id }}" value="{{ $key->id }}" {{ ($sale->sales_type_id == $key->id)?'checked="checked"':null }}>
									{{ $key->order_type }}
								</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('special_request') ? 'has-error' : '' }}">
					<div class="col form-check">
						<input type="checkbox" name="spec_req" class="form-check-input m-1" value="1" id="specReq" {{ !is_null($sale->special_request)?'checked="checked"':null }}>
						<label class="form-check-label col" for="specReq">
							Special Request
						</label>
					</div>
					<div class="form-group col-sm-8" id="wraptextarea">
						@if(!is_null($sale->special_request))
							{{ Form::textarea('special_request', @$value, ['class' => 'form-control form-control-sm', 'id' => 'sreq', 'placeholder' => 'Special Request Remarks']) }}
						@endif
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
					{{ Form::label( 'urgency1', 'Mark As Urgent : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col-sm-8">
						<div class="form-check">
							{{ Form::checkbox('urgency', 1, @$value, ['class' => 'form-check-input m-1', 'id' => 'urgency1']) }}
							<label class="form-check-label col-sm-4 " for="urgency1">
								Yes
							</label>
						</div>
					</div>
				</div>
				<div class="form-group row m-2 {{ $errors->has('sales_delivery_id.*') ? 'has-error' : '' }}">
					{{ Form::label( 'devi', 'Delivery Instruction : ', ['class' => 'col-sm-4 col-form-label'] ) }}
					<div class="col">
<?php
$sdt = $sale->belongstomanydelivery()->get();
foreach($sdt as $t) {
	$sdts[] = $t->id;
}
?>
						@foreach(\App\Models\Sales\OptSalesDeliveryType::all() as $key)
							<div class="form-check form-check-inline m-1">
								<label class="form-check-label" for="dbdid{{ $key->id }}">
									<input class="form-check-input m-1" type="checkbox" name="sales_delivery_id[]" id="dbdid{{ $key->id }}" value="{{ $key->id }}" {{ in_array($key->id, $sdts)?'checked="checked"':null }}>
									{{ $key->delivery_type }}
								</label>
							</div>
						@endforeach
						{{ Form::textarea('special_delivery_instruction', @$value, ['class' => 'form-control form-control-sm m-1', 'id' => 'sdev', 'placeholder' => 'Special Delivery Instruction', 'autocomplete' => 'off']) }}
					</div>
				</div>
			</div>

			<h5>Job Description</h5>
			<div class="col-sm-12">
				<div class="row jdesc_wrap">
@if($sale->hasmanyjobdescription()->get()->count())
<?php $m = 4 ?>
	@foreach($sale->hasmanyjobdescription()->get() as $va => $ke)
					<input type="hidden" name="jobdesc[{{ $m }}][id]" value="{{ $ke->id }}">
					<div class="col-sm-12 row border border-info mb-3 rounded">
						<div class="col-auto m-1 p-1">
							<button type="button" class="btn btn-sm btn-outline-secondary jdesc_remove" data-id="{{ $ke->id }}">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>
						<div class="col m-1 p-1 form-group {{ $errors->has('jobdesc.*.job_description') ? 'has-error' : '' }}">
							<textarea name="jobdesc[{{ $m }}][job_description]" id="jdi_{{ $m }}" class="form-control form-control-sm" placeholder="Job Description">{{ $ke->job_description }}</textarea>
						</div>
						<div class="col-auto m-1 p-1 row form-group {{ $errors->has('jobdesc.*.quantity') ? 'has-error' : '' }}">
							<div class="col">
								<input type="text" name="jobdesc[{{ $m }}][quantity]" id="jdq_{{ $m }}" value="{{ $ke->quantity }}" class="form-control form-control-sm m-1" placeholder="Quantity">
							</div>
							<div class="col form-group align-items-center {{ $errors->has('jobdesc.*.uom_id') ? 'has-error' : '' }}">
								<select name="jobdesc[{{ $m }}][uom_id]" id="jdu_{{ $m }}" class="form-select form-select-sm m-1" placeholder="UOM"></select>
							</div>
						</div>
						<div class="col-auto m-1 p-1 form-group {{ $errors->has('jobdesc.*.sales_get_item_id') ? 'has-error' : '' }}">
<?php
$sgji = $ke->hasmanyjobdescriptiongetitem()->get();
foreach ($sgji as $c) {
	$r[$va][] = $c->sales_get_item_id;
}
// dump($r[$va], $sgji);
$trv = $r[$va]??[];
?>
							@foreach(\App\Models\Sales\OptSalesGetItem::all() as $key)
								<div class="form-check">
									<input type="checkbox" name="jobdesc[{{ $m }}][sales_get_item_id][]" class="form-check-input" value="{{ $key->id }}" id="jdescitem_{{ $key->id.$m }}" {{ in_array($key->id, $trv)?'checked="checked"':null }}>
									<label class="form-check-label" for="jdescitem_{{ $key->id.$m }}">{{ $key->get_item }}</label>
								</div>
							@endforeach
						</div>
						<div class="col-sm-12 m-1 p-1 row">
							<div class="col-sm-5 row m-1 p-1 form-group {{ $errors->has('jobdesc.*.machine_id') ? 'has-error' : '' }}">
								<div class="col align-items-center">
									<select name="jobdesc[{{ $m }}][machine_id]" id="jobdescmach_{{ $m }}" class="form-select form-select-sm m-1" placeholder="Machine"></select>
								</div>
								<div class="col form-group align-items-center {{ $errors->has('jobdesc.*.machine_accessories_id') ? 'has-error' : '' }}">
									<select name="jobdesc[{{ $m }}][machine_accessory_id]" id="jobdescmachacc_{{ $m }}" class="form-select form-select-sm m-1" placeholder="Machine Accessories">
										<option value="" ></option>
										@foreach(\App\Models\Sales\OptMachineAccessory::all() as $k)
											<option value="{{ $k->id }}" class="{{ $k->machine_id }}" {{ ($ke->machine_accessory_id == $k->id)?'selected="selected"':null }}>{{ $k->accessory }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-sm-6 m-1 p-1">
								<div class="col m-1 p-1  form-group {{ $errors->has('jobdesc.*.remarks') ? 'has-error' : '' }}">
									<textarea name="jobdesc[{{ $m }}][remarks]" id="jdr_{{ $m }}" class="form-control form-control-sm" placeholder="Remarks">{{ $ke->remarks }}</textarea>
								</div>
							</div>
						</div>
					</div>
	<?php $m++ ?>
	@endforeach
@endif

				</div>
				<button type="button" class="btn btn-sm btn-outline-secondary jdesc_add"><i class="fa-solid fa-list-check"></i>&nbsp;Add Job Description</button>
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
// date
$('#nam, #delivery').datetimepicker({
	format:'YYYY-MM-DD',
	// useCurrent: false,
}).on('dp.change', function(e){
	$('#form').bootstrapValidator('revalidateField', 'date_order');
	$('#form').bootstrapValidator('revalidateField', 'delivery_at');
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
				// id: {{ $sale->customer_id }}
			}
			return query;
		}
	},
});

// Fetch the preselected item, and add to the control
var studentSelect = $('#cust');
$.ajax({
	type: 'POST',
	url: '{{ route('customer.customer') }}',
	data: {
			_token: '{!! csrf_token() !!}',
			id: {{ $sale->customer_id }}
	},
}).then(function (data) {
	console.log(data.results[0].id, data.results[0].text);
	// create the option and append to Select2
	var option = new Option(data.results[0].text, data.results[0].id, true, true);
	studentSelect.append(option).trigger('change');
});

/////////////////////////////////////////////////////////////////////////////////////////
// special request description
$('#specReq').change(function() {
	if(this.checked == true) {
		if ($('#sreq').length == 0) {
			$('#wraptextarea').append(
				'{{ Form::textarea('special_request', @$value, ['class' => 'form-control form-control-sm', 'id' => 'sreq', 'placeholder' => 'Special Request Remarks']) }}'
			);
			$('#form').bootstrapValidator('addField', $('#wraptextarea').find('[name="special_request"]'));
			// $('#wraptextarea').find('[name="special_request"]').css('border', '5px solid black');
		}
	} else {
		$('#sreq').remove();
		$('#form').bootstrapValidator('removeField', $('#wraptextarea').find('[name="special_request"]'));
	}
});

/////////////////////////////////////////////////////////////////////////////////////////
// select2
@if($sale->hasmanyjobdescription()->get()->count())
<?php $mu = 4 ?>
	@foreach($sale->hasmanyjobdescription()->get() as $va => $ke)
		$('#jdu_{{$mu}}').select2({
			placeholder: 'UOM',
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
		$.ajax({
			type: 'POST',
			url: '{{ route('uom.uom') }}',
			data: {
					_token: '{!! csrf_token() !!}',
					id: {{ $ke->uom_id }}
			},
		}).then(function (data) {
			console.log(data.results[0].id, data.results[0].text);
			// create the option and append to Select2
			var option = new Option(data.results[0].text, data.results[0].id, true, true);
			$('#jdu_{{$mu}}').append(option).trigger('change');
		});

		$('#jobdescmach_{{$mu}}').select2({
			placeholder: 'Machine',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('machine.machine') }}',
				type: 'GET',
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
		$.ajax({
			type: 'GET',
			url: '{{ route('machine.machine') }}',
			data: {
					_token: '{!! csrf_token() !!}',
					id: {{ $ke->machine_id }}
			},
		}).then(function (data) {
			console.log(data.results[0].id, data.results[0].text);
			// create the option and append to Select2
			var option = new Option(data.results[0].text, data.results[0].id, true, true);
			$('#jobdescmach_{{$mu}}').append(option).trigger('change');
		});

		$('#jobdescmachacc_{{$mu}}').select2({
			placeholder: 'Machine Accessories',
			allowClear: true,
			closeOnSelect: true,
		});

		$('#jobdescmachacc_{{$mu}}').chainedTo('#jobdescmach_{{$mu}}');

		<?php $mu++ ?>
	@endforeach
@endif

/////////////////////////////////////////////////////////////////////////////////////////
// select chained
// $('#jobdescmachacc_1').remoteChained({
// 	parents: '#jobdescmach_1',
// 	url: '{{ route('machineaccessories.machineaccessories') }}',
// });

/////////////////////////////////////////////////////////////////////////////////////////
// add item
var crb_max_fields = 504;						//maximum input boxes allowed
var crb_add_buttons = $(".jdesc_add");
var crb_wrappers = $(".jdesc_wrap");

var xcrb = {{ ($sale->hasmanyjobdescription()->get()->count())?$sale->hasmanyjobdescription()->get()->count() + 3:4 }};
$(crb_add_buttons).click(function(){
	// e.preventDefault();

	//max input box allowed
	if(xcrb < crb_max_fields){
		xcrb++;
		crb_wrappers.append(
			'<div class="col-sm-12 row border border-info mb-3 rounded">' +
				'<div class="col-auto m-1 p-1">' +
					'<button type="button" class="btn btn-sm btn-outline-secondary jdesc_remove">' +
						'<i class="fas fa-trash" aria-hidden="true"></i>' +
					'</button>' +
				'</div>' +
				'<div class="col m-1 p-1 form-group {{ $errors->has('jobdesc.*.job_description') ? 'has-error' : '' }}">' +
					'<textarea name="jobdesc[' + xcrb + '][job_description]" id="jdi_' + xcrb + '" class="form-control form-control-sm" placeholder="Job Description"></textarea>' +
				'</div>' +
				'<div class="col-auto m-1 p-1 row form-group {{ $errors->has('jobdesc.*.quantity') ? 'has-error' : '' }}">' +
					'<div class="col">' +
						'<input type="text" name="jobdesc[' + xcrb + '][quantity]" id="jdq_' + xcrb + '" class="form-control form-control-sm m-1" placeholder="Quantity">' +
					'</div>' +
					'<div class="col form-group {{ $errors->has('jobdesc.*.uom_id') ? 'has-error' : '' }}">' +
						'<select name="jobdesc[' + xcrb + '][uom_id]" id="jdu_' + xcrb + '" class="form-select form-select-sm m-1" placeholder="UOM"></select>' +
					'</div>' +
				'</div>' +
				'<div class="col-auto m-1 p-1 form-group {{ $errors->has('jobdesc.*.sales_get_item_id.*') ? 'has-error' : '' }}">' +
					@foreach(\App\Models\Sales\OptSalesGetItem::all() as $key)
						'<div class="form-check">' +
							'<input type="checkbox" name="jobdesc[' + xcrb + '][sales_get_item_id][]" class="form-check-input" value="{{ $key->id }}" id="' + xcrb + '_jdescitem_{{ $key->id }}">' +
							'<label class="form-check-label" for="' + xcrb + '_jdescitem_{{ $key->id }}">{{ $key->get_item }}</label>' +
						'</div>' +
					@endforeach
				'</div>' +
				'<div class="col-sm-12 m-1 p-1 row">' +
					'<div class="col-sm-5 row m-1 p-1 form-group {{ $errors->has('jobdesc.*.machine_id') ? 'has-error' : '' }}">' +
						'<div class="col">' +
							'<select name="jobdesc[' + xcrb + '][machine_id]" id="jobdescmach_' + xcrb + '" class="form-select form-select-sm m-1" placeholder="Machine"></select>' +
						'</div>' +
						'<div class="col form-group {{ $errors->has('jobdesc.*.machine_accessory_id') ? 'has-error' : '' }}">' +
							'<select name="jobdesc[' + xcrb + '][machine_accessory_id]" id="jobdescmachacc_' + xcrb + '" class="form-select form-select-sm m-1" placeholder="Machine Accessory">' +
								'<option value="" ></option>' +
								@foreach(\App\Models\Sales\OptMachineAccessory::all() as $k)
									'<option value="{{ $k->id }}" class="{{ $k->machine_id }}">{{ $k->accessory }}</option>' +
								@endforeach
							'</select>' +
						'</div>' +
					'</div>' +
					'<div class="col-sm-6 m-1 p-1">' +
						'<div class="col m-1 p-1  form-group {{ $errors->has('jobdesc.*.remarks') ? 'has-error' : '' }}">' +
							'<textarea name="jobdesc[' + xcrb + '][remarks]" id="jdr_' + xcrb + '" class="form-control form-control-sm" placeholder="Remarks"></textarea>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>'
		);

		// $('.form-check').find('[name="jobdesc[' + xcrb + '][sales_get_item_id][]"]').css('border', '3px solid red');

		$('#jdu_' + xcrb ).select2({
			placeholder: 'UOM',
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

		$('#jobdescmach_' + xcrb).select2({
			placeholder: 'Machine',
			// theme: 'bootstrap5',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('machine.machine') }}',
				type: 'GET',
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

		$('#jobdescmachacc_' + xcrb).select2({
			placeholder: 'Machine Accessories',
			// theme: 'bootstrap5',
			allowClear: true,
			closeOnSelect: true,
		});

		$('#jobdescmachacc_' + xcrb).chainedTo('#jobdescmach_' + xcrb);

		//bootstrap validate
		// $('#form').bootstrapValidator('addField', $('.crossbackup_row').find('[name="crossbackup['+ xcrb +'][backup_staff_id]"]').css('border', '3px solid black'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][job_description]"]'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][quantity]"]'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][uom_id]"]'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][sales_get_item_id][]"]'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][machine_id]"]'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][machine_accessory_id]"]'));
		$('#form').bootstrapValidator('addField', $('.form-group').find('[name="jobdesc[' + xcrb + '][remarks]"]'));
	}
})

$(crb_wrappers).on("click",".jdesc_remove", function(e){
	//user click on remove text
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="jobdesc[' + xcrb + '][job_description]"]');
	var $option2 = $row.find('[name="jobdesc[' + xcrb + '][quantity]"]');
	var $option3 = $row.find('[name="jobdesc[' + xcrb + '][uom_id]"]');
	var $option4 = $row.find('[name="jobdesc[' + xcrb + '][sales_get_item_id][]"]');
	var $option5 = $row.find('[name="jobdesc[' + xcrb + '][machine_id]"]');
	var $option6 = $row.find('[name="jobdesc[' + xcrb + '][machine_accessories_id]"]');
	var $option7 = $row.find('[name="jobdesc[' + xcrb + '][remarks]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	$('#form').bootstrapValidator('removeField', $option4);
	$('#form').bootstrapValidator('removeField', $option5);
	$('#form').bootstrapValidator('removeField', $option6);
	$('#form').bootstrapValidator('removeField', $option7);
	xcrb--;
})

/////////////////////////////////////////////////////////////////////////////////////////
// validator
$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {

		date_order: {
			validators: {
				notEmpty: {
					message: 'Please insert'
				},
			}
		},
		customer_id: {
			validators: {
				// notEmpty: {
				// 	message: 'Please choose'
				// },
			}
		},
		'sales_type_id': {
			validators: {
				notEmpty: {
					message: 'Please choose'
				},
			}
		},
		special_request: {
			validators: {
				notEmpty: {
					message: 'Please insert'
				},
			}
		},
		po_number: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert'
				// },
			}
		},
		delivery_at: {
			validators: {
				// notEmpty: {
				// 	message: 'Please insert'
				// },
			}
		},
		urgency: {
			validators: {
				// notEmpty: {
				// 	message: 'Please choose'
				// },
			}
		},
		'sales_delivery_id[]': {
			validators: {
				notEmpty: {
					message: 'Please choose'
				},
			}
		},
		special_delivery_instruction: {
			validators: {
				// notEmpty: {
				// 	message: 'Please choose'
				// },
			}
		},
		@for($i = 4; $i < 504; ++$i)
		'jobdesc[{{ $i }}][job_description]': {
			validators: {
				notEmpty: {
					message: 'Please insert'
				},
			}
		},
		'jobdesc[{{ $i }}][quantity]': {
			validators: {
				notEmpty: {
					message: 'Please insert'
				},
			}
		},
		'jobdesc[{{ $i }}][uom_id]': {
			validators: {
				notEmpty: {
					message: 'Please choose'
				},
			}
		},
		// 'jobdesc[{{ $i }}][sales_get_item_id][]': {
		// 	validators: {
		// 		notEmpty: {
		// 			// message: 'Please choose'
		// 		},
		// 		choice: {
		// 			min: 1,
		// 			message: 'Please choose 1 - 3 options'
		// 		}
		// 	}
		// },
		'jobdesc[{{ $i }}][machine_id]': {
			validators: {
				notEmpty: {
					message: 'Please choose'
				},
			}
		},
		'jobdesc[{{ $i }}][machine_accessory_id]': {
			validators: {
				// notEmpty: {
				// 	message: 'Please choose'
				// },
			}
		},
		'jobdesc[{{ $i }}][remarks]': {
			// validators: {
			// 	notEmpty: {
			// 		message: 'Please insert'
			// 	},
			// }
		},
		@endfor

	}
})
.find('[name="reason"]')
// .ckeditor()
// .editor
	.on('change', function() {
		// Revalidate the bio field
	$('#form').bootstrapValidator('revalidateField', 'reason');
	// console.log($('#reason').val());
});


/////////////////////////////////////////////////////////////////////////////////////////
@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection


