@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\OptWorkingHour;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Generate Working Hour For A Year</h4>
	{!! Form::open(['route' => ['workinghour.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}
	<div class="form-group row {{ ($errors->has('effective_date_start') || $errors->has('effective_date_end')) ? ' has-error' : '' }} mb-3 g-3">
		{{ Form::label( 'yea', 'Ramadhan Duration : ', ['class' => 'col-sm-2 col-form-label'] ) }}
		<div class="col-sm-5">
			{{ Form::text('effective_date_start', @$value, ['class' => 'form-control col-auto', 'id' => 'effective_date_start', 'placeholder' => 'Ramadhan Start', 'autocomplete' => 'off']) }}
		</div>
		<div class="col-sm-5">
			{{ Form::text('effective_date_end', @$value, ['class' => 'form-control col-auto', 'id' => 'effective_date_end', 'placeholder' => 'Ramadhan End', 'autocomplete' => 'off']) }}
		</div>
	</div>

	<div class="form-group row">
		<div class="col-sm-10 offset-sm-2">
			{!! Form::button('Generate Next Year Working Hour', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
		</div>
	</div>
	{{ Form::close() }}

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
//date
$('#effective_date_start').datetimepicker({
	format: 'YYYY-MM-DD'
})
.on("dp.change dp.show dp.update", function (e) {
	var minDate = $('#effective_date_start').val();
	$('#effective_date_end').datetimepicker('minDate', minDate);
	$('#form').bootstrapValidator('revalidateField', 'effective_date_start');
});


$('#effective_date_end').datetimepicker({
	format: 'YYYY-MM-DD',
	useCurrent: false //Important! See issue #1075
})
.on("dp.change dp.show dp.update", function (e) {
	var maxDate = $('#effective_date_end').val();
	$('#effective_date_start').datetimepicker('maxDate', maxDate);
	$('#form').bootstrapValidator('revalidateField', 'effective_date_end');
});


/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator

$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {
		'effective_date_start': {
			validators: {
				notEmpty: {
					message: 'Please insert ramadhan date start. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert ramadhan date start. '
				},
				remote: {
					type: 'POST',
					url: '{{ route('yearworkinghourstart') }}',
					message: 'The duration of Ramadhan month for this year is already exist. Please choose another year',
					data: function(validator) {
								return {
											_token: '{!! csrf_token() !!}',
											effective_date_start: $('#effective_date_start').val(),
								};
							},
					delay: 1,		// wait 0.001 seconds
				},
			}
		},
		'effective_date_end': {
			validators: {
				notEmpty: {
					message: 'Please insert ramadhan date end. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert ramadhan date end. '
				},
				remote: {
					type: 'POST',
					url: '{{ route('yearworkinghourend') }}',
					message: 'The duration of Ramadhan month for this year is already exist. Please choose another year',
					data: function(validator) {
								return {
											_token: '{!! csrf_token() !!}',
											effective_date_end: $('#effective_date_end').val(),
								};
							},
					// delay: 1,		// wait 0.001 seconds
				},
			}
		},
	}
});
@endsection
