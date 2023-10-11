<?php
use App\Models\Staff;
use App\Models\HumanResources\HROvertimeRange;

?>
@extends('layouts.app')

@section('content')
<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Edit Staff Overtime</h4>
	{{ Form::model($overtime, ['route' => ['overtime.update', $overtime->id], 'method' => 'PATCH', 'id' => 'form', 'autocomplete' => 'off', 'class' => 'form-horizontal', 'files' => true]) }}

	<div class="row justify-content-center">
		<div class="col-sm-6 gy-1 gx-1 align-items-start">

			<div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
				{{ Form::label( 'nam', 'Date Overtime : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::text('ot_date', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'nam', 'placeholder' => 'Date Overtime', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('overtime_range_id') ? 'has-error' : '' }}">
				{{ Form::label( 'mar', 'Overtime : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					<select name="overtime_range_id" id="mar" class="form-select form-select-sm col-auto" placeholder="Marital Status">
						<option value="">Please choose</option>
						@foreach(HROvertimeRange::where('active', 1)->get() as $key)
							<option value="{{ $key->id }}" {{ ($key->id == $overtime->overtime_range_id)?'selected':NULL }}>{{ \Carbon\Carbon::parse($key->start)->format('g:i a') }} <=> {{ \Carbon\Carbon::parse($key->end)->format('g:i a') }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rel', 'Staff : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto">
					{{ Form::select('staff_id', Staff::where('active', 1)->pluck('name', 'id')->toArray(), @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'rel', 'placeholder' => 'Please Choose', 'autocomplete' => 'off', 'multiple']) }}
				</div>
			</div>

	<div class="offset-4 mb-6">
		{!! Form::submit('Update Staff Overtime', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
	</div>

	{{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
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
	format: 'YYYY-MM-DD',
	useCurrent: true,
});

$('#mar, #rel').select2({
	placeholder: 'Please Select',
	width: '100%',
	allowClear: true,
	closeOnSelect: true,
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
		ot_date: {
			validators: {
				notEmpty: {
					message: 'Please insert password. '
				},
			}
		},
		staff_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
		overtime_range_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
	}
});

@endsection
