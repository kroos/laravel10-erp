@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\OptWorkingHour;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Add Holiday Calendar</h4>
	{{ Form::model( $holidaycalendar, ['route' => ['holidaycalendar.update', $holidaycalendar->id], 'method' => 'PATCH', 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) }}
		<div class="row mb-3 g-3 " style="position: relative">
			{{ Form::label( 'yea', 'Date Range : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="form-group col-sm-5 {{ $errors->has('date_start')?'has-error':'' }}">
				{{ Form::text('date_start', @$value, ['class' => 'form-control col-auto', 'id' => 'dstart', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
			</div>
			<div class="form-group col-sm-5{{ $errors->has('date_end')?'has-error':'' }}">
				{{ Form::text('date_end', @$value, ['class' => 'form-control col-auto', 'id' => 'dend', 'placeholder' => 'Date End', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group row mb-3 g-3 {{ $errors->has('holiday')?'has-error':'' }}">
			{{ Form::label( 'hol', 'Holiday : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{{ Form::text('holiday', @$value, ['class' => 'form-control col-auto', 'id' => 'hol', 'placeholder' => 'Holiday', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group row mb-3 g-3 {{ $errors->has('remarks')?'has-error':'' }}">
			{{ Form::label( 'rem', 'Holiday : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{{ Form::textarea('remarks', @$value, ['class' => 'form-control col-auto', 'id' => 'rem', 'placeholder' => 'Remarks', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group row mb-3 g-3">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Add Holiday', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
			</div>
		</div>
	{{ Form::close() }}

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#dstart').datetimepicker({
	format: 'YYYY-MM-DD',
	useCurrent: false, //Important! See issue #1075
})
.on("dp.change dp.show dp.update", function (e) {
	var minDate = $('#dstart').val();
	$('#dend').datetimepicker('minDate', minDate);
	$('#form').bootstrapValidator('revalidateField', 'date_start');
});


$('#dend').datetimepicker({
	format: 'YYYY-MM-DD',
	useCurrent: false, //Important! See issue #1075
})
.on("dp.change dp.show dp.update", function (e) {
	var maxDate = $('#dend').val();
	$('#dstart').datetimepicker('maxDate', maxDate);
	$('#form').bootstrapValidator('revalidateField', 'date_end');
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
		'date_start': {
			validators: {
				notEmpty: {
					message: 'Please insert holiday date start. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert holiday date start. '
				},
				// remote: {
				// 	type: 'POST',
				// 	url: '{{ route('hcaldstart') }}',
				// 	message: 'The date is already exist. Please choose another date. ',
				// 	data: function(validator) {
				// 				return {
				// 							_token: '{!! csrf_token() !!}',
				// 							date_start: $('#dstart').val(),
				// 				};
				// 			},
				// 	//delay: 1,		// wait 0.001 seconds
				// },
			}
		},
		'date_end': {
			validators: {
				notEmpty: {
					message: 'Please insert holiday date end. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert holiday date end. '
				},
				// remote: {
				// 	type: 'POST',
				// 	url: '{{ route('hcaldend') }}',
				// 	message: 'The date is already exist. Please choose another date. ',
				// 	data: function(validator) {
				// 				return {
				// 							_token: '{!! csrf_token() !!}',
				// 							date_end: $('#dend').val(),
				// 				};
				// 			},
				// 	delay: 1,		// wait 0.001 seconds
				// },
			}
		},
		'holiday': {
			validators: {
				notEmpty: {
					message: 'Please insert the name of the holiday. '
				}
			}
		},
	}
});

@endsection
