@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\OptWorkingHour;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Edit Annual Leave Entitlement Year {{ $annualleave->year }} for {{ $annualleave->belongstostaff->name }}</h4>
	{{ Form::model($annualleave, ['route' => ['annualleave.update', $annualleave->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

		<div class="form-group row {{ $errors->has('annual_leave') ? 'has-error' : '' }} mb-3 g-3">
			{{ Form::label( 'alt', 'Annual Leave : ', ['class' => 'col-sm-3 col-form-label'] ) }}
			<div class=" col-sm-2">
				{{ Form::text('annual_leave', @$value, ['class' => 'form-control form-control-sm', 'id' => 'alt', 'placeholder' => 'Annual Leave Initialize', 'autocomplete' => 'off', 'readOnly']) }}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('annual_leave_adjustment') ? 'has-error' : '' }} mb-3 g-3">
			{{ Form::label( 'ala', 'Annual Leave Adjustment : ', ['class' => 'col-sm-3 col-form-label'] ) }}
			<div class=" col-sm-2">
				{{ Form::number('annual_leave_adjustment', @$value, ['class' => 'form-control form-control-sm', 'id' => 'ala', 'placeholder' => 'Annual Leave Adjustment', 'step' => '0.5', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('annual_leave_utilize') ? 'has-error' : '' }} mb-3 g-3">
			{{ Form::label( 'alu', 'Annual Leave Utilize : ', ['class' => 'col-sm-3 col-form-label'] ) }}
			<div class=" col-sm-2">
				{{ Form::text('annual_leave_utilize', @$value, ['class' => 'form-control form-control-sm', 'id' => 'alu', 'placeholder' => 'Annual Leave Utilize', 'autocomplete' => 'off', 'readOnly']) }}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('annual_leave_balance') ? 'has-error' : '' }} mb-3 g-3">
			{{ Form::label( 'alb', 'Annual Leave Balance : ', ['class' => 'col-sm-3 col-form-label'] ) }}
			<div class=" col-sm-2">
				{{ Form::text('annual_leave_balance', @$value, ['class' => 'form-control form-control-sm', 'id' => 'alb', 'placeholder' => 'Annual Leave Balance', 'autocomplete' => 'off', 'readOnly']) }}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('remarks') ? 'has-error' : '' }} mb-3 g-3">
			{{ Form::label( 'rem', 'Remarks : ', ['class' => 'col-sm-3 col-form-label'] ) }}
			<div class=" col-sm-4">
				{{ Form::textarea('remarks', @$value, ['class' => 'form-control form-control-sm', 'id' => 'rem', 'placeholder' => 'Remarks', 'autocomplete' => 'off']) }}
			</div>
		</div>



		<div class="form-group row  mb-3 g-3">
			<div class="col-sm-10 offset-sm-3">
				{!! Form::button('Submit', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
			</div>
		</div>

	{{ Form::close() }}

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// counting annual leave
$(document).on('keyup mouseup', '#ala', function () {
	var balance = (($(this).val() * 100)/100) + {{ $annualleave->annual_leave_balance }};
	$('#alb').val(balance);
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
			annual_leave: {
				validators: {
					notEmpty: {
						message: 'Please insert number with/out decimal. ',
					},
					numeric: {
						separator: '.',
						message: 'Use DOT (.) as separator. '
					}
				}
			},
			annual_leave_adjustment: {
				validators: {
					notEmpty: {
						message: 'Please insert number with/out decimal. ',
					},
					numeric: {
						separator: '.',
						message: 'Use DOT (.) as separator. '
					}
				}
			},
			annual_leave_utilize: {
				validators: {
					notEmpty: {
						message: 'Please insert number with/out decimal. ',
					},
					numeric: {
						separator: '.',
						message: 'Use DOT (.) as separator. '
					}
				}
			},
			annual_leave_balance: {
				validators: {
					notEmpty: {
						message: 'Please insert number with/out decimal. ',
					},
					numeric: {
						separator: '.',
						message: 'Use DOT (.) as separator. '
					}
				}
			},
			remarks: {
				validators: {
					// notEmpty: {
					// 	message: 'Please type some remarks',
					// },
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
