<?php
use App\Models\Staff;
use App\Models\HumanResources\OptReligion;
use App\Models\HumanResources\OptGender;
use App\Models\HumanResources\OptRace;
use App\Models\HumanResources\OptMaritalStatus;
use App\Models\HumanResources\OptCountry;
?>
@extends('layouts.app')

@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Add Staff</h4>
	{{ Form::open(['route' => ['staff.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

	<div class="row justify-content-center">

		<div class="col-sm-6 row gy-1 gx-1 align-items-start">
			<div class="form-group row mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
				{{ Form::label( 'nam', 'Name : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('name', @$value, ['class' => 'form-control col-auto', 'id' => 'nam', 'placeholder' => 'Name', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('ic') ? 'has-error' : '' }}">
				{{ Form::label( 'ic', 'Identity Card/Passport : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('ic', @$value, ['class' => 'form-control col-auto', 'id' => 'ic', 'placeholder' => 'Identity Card/Passport', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rel', 'Religion : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('religion_id', OptReligion::pluck('religion', 'id')->toArray(), @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'rel', 'placeholder' => 'Religion', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('gender_id') ? 'has-error' : '' }}">
				{{ Form::label( 'gen', 'Gender : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('gender_id', OptGender::pluck('gender', 'id')->toArray(), @$value, ['class' => 'form-control col-auto', 'id' => 'gen', 'placeholder' => 'Gender', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('race_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rac', 'Race : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('race_id', OptRace::pluck('race', 'id')->toArray(), @$value, ['class' => 'form-control col-auto', 'id' => 'rac', 'placeholder' => 'Race', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('nationality_id') ? 'has-error' : '' }}">
				{{ Form::label( 'nat', 'Nationality : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('nationality_id', OptCountry::pluck('country', 'id')->toArray(), @$value, ['class' => 'form-control col-auto', 'id' => 'nat', 'placeholder' => 'Nationality', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('marital_status_id') ? 'has-error' : '' }}">
				{{ Form::label( 'mar', 'Marital Status : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('marital_status_id', OptMaritalStatus::pluck('marital_status', 'id')->toArray(), @$value, ['class' => 'form-control col-auto', 'id' => 'mar', 'placeholder' => 'Marital Status', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
				{{ Form::label( 'ema', 'Email : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('email', @$value, ['class' => 'form-control col-auto', 'id' => 'ema', 'placeholder' => 'Email', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('address') ? 'has-error' : '' }}">
				{{ Form::label( 'add', 'Address : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::textarea('address', @$value, ['class' => 'form-control col-auto', 'id' => 'add', 'placeholder' => 'Address', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
				{{ Form::label( 'mob', 'Mobile : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('mobile', @$value, ['class' => 'form-control col-auto', 'id' => 'mob', 'placeholder' => 'Mobile', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
				{{ Form::label( 'pho', 'Phone : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('phone', @$value, ['class' => 'form-control col-auto', 'id' => 'pho', 'placeholder' => 'Phone', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('dob') ? 'has-error' : '' }}">
				{{ Form::label( 'dob', 'Date Of Birth : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('dob', @$value, ['class' => 'form-control col-auto', 'id' => 'dob', 'placeholder' => 'Date Of Birth', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('cimb_account') ? 'has-error' : '' }}">
				{{ form::label( 'cia', 'CIMB Account : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('cimb_account', @$value, ['class' => 'form-control col-auto', 'id' => 'cia', 'placeholder' => 'CIMB Account', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('epf_account') ? 'has-error' : '' }}">
				{{ form::label( 'epf', 'EPF Account : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('epf_account', @$value, ['class' => 'form-control col-auto', 'id' => 'epf', 'placeholder' => 'EPF Account', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('income_tax_no') ? 'has-error' : '' }}">
				{{ form::label( 'itn', 'Income Tax No : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('income_tax_no', @$value, ['class' => 'form-control col-auto', 'id' => 'itn', 'placeholder' => 'Income Tax No', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('socso_no') ? 'has-error' : '' }}">
				{{ form::label( 'son', 'SOCSO No : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('socso_no', @$value, ['class' => 'form-control col-auto', 'id' => 'son', 'placeholder' => 'SOCSO No', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('weight') ? 'has-error' : '' }}">
				{{ form::label( 'wei', 'Weight : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('weight', @$value, ['class' => 'form-control col-auto', 'id' => 'wei', 'placeholder' => 'Weight', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('height') ? 'has-error' : '' }}">
				{{ form::label( 'hei', 'Height : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('height', @$value, ['class' => 'form-control col-auto', 'id' => 'hei', 'placeholder' => 'Height', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('join') ? 'has-error' : '' }}">
				{{ form::label( 'jpo', 'Date Join : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto">
					{{ form::text('join', @$value, ['class' => 'form-control col-auto', 'id' => 'jpo', 'placeholder' => 'Date Join', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('image') ? 'has-error' : '' }}">
				{{ Form::label( 'ima', 'Image : ', ['class' => 'col-sm-5 col-form-label'] ) }}
				<div class="col-auto supportdoc">
					{{ Form::file( 'image', ['class' => 'form-control form-control-file', 'id' => 'ima', 'placeholder' => 'Image']) }}
				</div>
			</div>
		</div>

		<div class="col-sm-6 row gy-1 gx-1 align-items-start">

			<div class="row row-cols-auto">
				<div class="row">
					<div class="col-auto">
						<h6>Staff Spouse</h6>
					</div>
					<div class="col-auto">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary spouse_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add More Spouse
						</button>
					</div>
				</div>
				<div class="row mb-1 g-1 spouse_wrap">
					<div class="row spouse_row">
						<div class="col-auto mb-1 g-1 ">
							<button class="btn btn-sm btn-outline-secondary spouse_remove" type="button">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.spouse') ? 'has-error' : '' }}">
							<input type="text" name="staffspouse[1][spouse]" id="spo" class="form-control form-control-sm" placeholder="Spouse">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.phone') ? 'has-error' : '' }}">
							<input type="text" name="staffspouse[1][phone]" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.profession') ? 'has-error' : '' }}">
							<input type="text" name="staffspouse[1][profession]" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">
						</div>
					</div>
				</div>
			</div>

			<div class="row row-cols-auto">
				<div class="row">
					<div class="col-auto">
						<h6>Staff Children</h6>
					</div>
					<div class="col-auto">
						<button type="button" class="col-auto btn btn-sm btn-outline-secondary children_add">
							<i class="fas fa-plus" aria-hidden="true"></i>&nbsp;Add More Children
						</button>
					</div>
				</div>
				<div class="row mb-1 g-1 children_wrap">
					<div class="row spouse_row">
						<div class="col-auto mb-1 g-1 ">
							<button class="btn btn-sm btn-outline-secondary children_remove" type="button">
								<i class="fas fa-trash" aria-hidden="true"></i>
							</button>
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.spouse') ? 'has-error' : '' }}">
							<input type="text" name="staffchildren[1][spouse]" id="spo" class="form-control form-control-sm" placeholder="Spouse">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.phone') ? 'has-error' : '' }}">
							<input type="text" name="staffchildren[1][phone]" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">
						</div>

						<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffchildren.*.profession') ? 'has-error' : '' }}">
							<input type="text" name="staffchildren[1][profession]" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">
						</div>
					</div>
				</div>
			</div>


		</div>
	</div>

	<div class="offset-6">
		{!! Form::submit('Add Staff', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
	</div>

	{{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#rel, #gen, #rac, #nat, #mar').select2({
	// placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
});


$('#dob, #jpo').datetimepicker({
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
	format: 'YYYY-MM-DD',
	useCurrent: true,
});

/////////////////////////////////////////////////////////////////////////////////////////
// add spouse : add and remove row

var max_fields  = 4;						//maximum input boxes allowed
var add_buttons	= $(".spouse_add");
var wrappers	= $(".spouse_wrap");

var xs = 1;
$(add_buttons).click(function(){
	// e.preventDefault();

//max input box allowed
if(xs < max_fields){
	xs++;
	wrappers.append(

		'<div class="row spouse_row">' +
			'<div class="col-auto mb-1 g-1 ">' +
				'<button class="btn btn-sm btn-outline-secondary spouse_remove" type="button">' +
					'<i class="fas fa-trash" aria-hidden="true"></i>' +
				'</button>' +
			'</div>' +
			'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.spouse') ? 'has-error' : '' }}">' +
				'<input type="text" name="staffspouse[' + xs + '][spouse]" id="spo" class="form-control form-control-sm" placeholder="Spouse">' +
			'</div>' +
			'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.phone') ? 'has-error' : '' }}">' +
				'<input type="text" name="staffspouse[' + xs + '][phone]" id="pho" class="form-control form-control-sm" placeholder="Spouse Phone">' +
			'</div>' +
			'<div class="col-auto mb-1 g-1 form-group {{ $errors->has('staffspouse.*.profession') ? 'has-error' : '' }}">' +
				'<input type="text" name="staffspouse[' + xs + '][profession]" id="pro" class="form-control form-control-sm" placeholder="Spouse Profession">' +
			'</div>' +
		'</div>'

	); //add input box

	//bootstrap validate
	$('#form').bootstrapValidator('addField',	$('.spouse_row')	.find('[name="staffspouse['+ xs +'][spouse]"]'));
	$('#form').bootstrapValidator('addField',	$('.spouse_row')	.find('[name="staffspouse['+ xs +'][phone]"]'));
	$('#form').bootstrapValidator('addField',	$('.spouse_row')	.find('[name="staffspouse['+ xs +'][profession]"]'));
}
})

$(wrappers).on("click",".spouse_remove", function(e){
	//user click on remove text
	e.preventDefault();
	var $row = $(this).parent().parent();
	var $option1 = $row.find('[name="staffspouse[' + xs + '][spouse]"]');
	var $option2 = $row.find('[name="staffspouse[' + xs + '][phone]"]');
	var $option3 = $row.find('[name="staffspouse[' + xs + '][profession]"]');
	$row.remove();

	$('#form').bootstrapValidator('removeField', $option1);
	$('#form').bootstrapValidator('removeField', $option2);
	$('#form').bootstrapValidator('removeField', $option3);
	console.log();
	xs--;
})

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {

@for ($ie = 1; $ie <= 4; $ie++)
		'staffspouse[{{ $ie }}][spouse]': {
			validators: {
				notEmpty: {
					message: 'Please insert spouse. '
				},
				regexp: {
					regexp: /^[a-z\s\'\@]+$/i,
					message: 'The full name can consist of alphabetical characters, \', @ and spaces only'
				},
			}
		},
		'staffspouse[{{ $ie }}][phone]': {
			validators: {
				notEmpty: {
					message: 'Please insert spouse phone. '
				},
				numeric: {
					message: 'Only numbers. '
				},
			}
		},
		'staffspouse[{{ $ie }}][profession]': {
			validators: {
				notEmpty: {
					message: 'Please insert spouse profession. '
				},
			}
		},
@endfor
	}
});

@endsection
