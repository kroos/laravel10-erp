@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\OptWorkingHour;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Edit Working Hour</h4>
	{{ Form::model($workinghour, ['route' => ['workinghour.update', $workinghour->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

			<div class="form-group row {{ $errors->has('time') ? 'has-error' : '' }} mb-3 g-3">
				{{ Form::label( 'dstart1', 'Time : ', ['class' => 'col-sm-2 col-form-label'] ) }}
				<div class=" col-sm-2">
					{{ Form::text('time_start_am', @$value, ['class' => 'form-control', 'id' => 'tsa', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
				</div>
				<div class="col-sm-2">
					{{ Form::text('time_end_am', @$value, ['class' => 'form-control', 'id' => 'tea', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
				</div>
				<div class="col-sm-2">
					{{ Form::text('time_start_pm', @$value, ['class' => 'form-control', 'id' => 'tsp', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
				</div>
				<div class="col-sm-2">
					{{ Form::text('time_end_pm', @$value, ['class' => 'form-control', 'id' => 'tep', 'tep' => 'Date Start', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row {{ $errors->has('date') ? 'has-error' : '' }}  mb-3 g-3">
				{{ Form::label( 'dstart2', 'Effective Date : ', ['class' => 'col-sm-2 col-form-label'] ) }}
				<div class="col-sm-5">
					{{ Form::text('effective_date_start', @$value, ['class' => 'form-control col-auto', 'id' => 'eds', 'placeholder' => 'Effective Date Start', 'autocomplete' => 'off']) }}
				</div>
				<div class="col-sm-5">
					{{ Form::text('effective_date_end', @$value, ['class' => 'form-control col-auto', 'id' => 'ede', 'placeholder' => 'Effective Date End', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row {{ $errors->has('time') ? 'has-error' : '' }}  mb-3 g-3">
				{{ Form::label( 'dstart3', 'Remarks : ', ['class' => 'col-sm-2 col-form-label'] ) }}
				<div class="col-sm-10">
					{{ Form::text('remarks', @$value, ['class' => 'form-control', 'id' => 'dstart', 'placeholder' => 'Remarks', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
				</div>
			</div>

			<div class="form-group row  mb-3 g-3">
				<div class="col-sm-10 offset-sm-2">
					{!! Form::button('Submit', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
				</div>
			</div>

	{{ Form::close() }}

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
// time
$('#tsa').datetimepicker({
	format: 'h:mm A',
	// enabledHours: [8, 9, 10, 11, 12],
	useCurrent: false, //Important! See issue #1075
})
.on('dp.change dp.show dp.update', function(){
	$('#form').bootstrapValidator('revalidateField', 'time_start_am');
});

$('#tea').datetimepicker({
	format: 'h:mm A',
	// enabledHours: [8, 9, 10, 11, 12],
	useCurrent: false, //Important! See issue #1075
})
.on('dp.change dp.show dp.update', function(){
	$('#form').bootstrapValidator('revalidateField', 'time_end_am');
});

$('#tsp').datetimepicker({
	format: 'h:mm A',
	// enabledHours: [13, 14, 15, 16, 17],
	useCurrent: false, //Important! See issue #1075
})
.on('dp.change dp.show dp.update', function(){
	$('#form').bootstrapValidator('revalidateField', 'time_start_pm');
});

$('#tep').datetimepicker({
	format: 'h:mm A',
	// enabledHours: [13, 14, 15, 16, 17],
	useCurrent: false, //Important! See issue #1075
})
.on('dp.change dp.show dp.update', function(){
	$('#form').bootstrapValidator('revalidateField', 'time_end_pm');
});

/////////////////////////////////////////////////////////////////////////////////////////
$('#eds').datetimepicker({
	format: 'YYYY-MM-DD',
	useCurrent: false, // Important! See issue #1075
})
.on('dp.change dp.show dp.update', function(){
	var mintar = $('#eds').val();
	$('#ede').datetimepicker( 'minDate', mintar );
	$('#form').bootstrapValidator('revalidateField', 'effective_date_start');
});

$('#ede').datetimepicker({
	format: 'YYYY-MM-DD',
	useCurrent: false, // Important! See issue #1075
})
.on('dp.change dp.show dp.update', function(){
	var maxtar = $('#ede').val();
	$('#eds').datetimepicker( 'maxDate', maxtar );
	$('#form').bootstrapValidator('revalidateField', 'effective_date_end');
});

/////////////////////////////////////////////////////////////////////////////////////////
// validator
$(document).ready(function() {
	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: '',
			invalid: '',
			validating: ''
		},
		fields: {
			time_start_am: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-5]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			time_end_am: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-5]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			time_start_pm: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-5]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			time_end_pm: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-6]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			effective_date_start: {
				validators: {
					notEmpty : {
						message: 'Please insert date start'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date. '
					},
				}
			},
			effective_date_end: {
				validators: {
					notEmpty : {
						message: 'Please insert date end'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date. '
					},
				}
			},
		}
	})
	// .find('[name="reason"]')
	// .ckeditor()
	// .editor
	// 	.on('change', function() {
	// 		// Revalidate the bio field
	// 	$('#form').bootstrapValidator('revalidateField', 'reason');
	// 	// console.log($('#reason').val());
	// })
	;
});
@endsection
