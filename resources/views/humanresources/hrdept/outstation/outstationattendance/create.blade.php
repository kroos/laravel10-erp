@extends('layouts.app')

@section('content')
<style>
	.scrollable-div {
		/* Set the width height as needed */
/*		width: 100%;*/
		height: 400px;
		background-color: blanchedalmond;
		/* Add scrollbars when content overflows */
		overflow: auto;
	}

	p {
		margin-top: 4px;
		margin-bottom: 4px;
	}
</style>

<?php
use \App\Models\HumanResources\OptWorkingHour;
use \App\Models\Staff;
use \App\Models\Customer;

use \Carbon\Carbon;

$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->where(function ($query) {
				$query->where('staffs.div_id', '!=', 2)
				->orWhereNull('staffs.div_id');
			})
			->select('staffs.id as staffID', 'staffs.*', 'logins.*')
			->orderBy('logins.username', 'asc')
			->get();

$c = Customer::orderBy('customer')->pluck('customer', 'id')->toArray();
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation</h4>
	{!! Form::open(['route' => ['outstation.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}

	<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
		<div class="col-md-2">
		{{Form::label('staff', 'Outstation Staff : ')}}
		</div>
		<div class="col-md-10">
			<div class="scrollable-div">
				@foreach ($staffs as $staff)
				<p>
					<input type="checkbox" name="staff_id[]" id="staff" value="{{ $staff->staffID }}">
					<label>{{ $staff->username }} - {{ $staff->name }}</label>
				</p>
				@endforeach
			</div>
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('customer_id') ? 'has-error' : '' }}">
		{{ Form::label( 'loc', 'Location : ', ['class' => 'col-sm-2 col-form-label'] ) }}
		<div class="col-md-10">
			{{ Form::select('customer_id', $c, @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'loc', 'placeholder' => 'Please choose', 'autocomplete' => 'off']) }}
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('date_from') ? 'has-error' : '' }}">
		{{ Form::label( 'from', 'From : ', ['class' => 'col-sm-2 col-form-label'] ) }}
		<div class="col-md-10" style="position: relative">
			{{ Form::text('date_from', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'from', 'placeholder' => 'Date From', 'autocomplete' => 'off']) }}
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('date_to') ? 'has-error' : '' }}">
		{{ Form::label( 'to', 'To : ', ['class' => 'col-sm-2 col-form-label'] ) }}
		<div class="col-md-10" style="position: relative">
			{{ Form::text('date_to', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'to', 'placeholder' => 'Date To', 'autocomplete' => 'off']) }}
		</div>
	</div>

	<div class="form-group row mb-3 {{ $errors->has('remarks') ? 'has-error' : '' }}">
		{{ Form::label( 'rem', 'Remarks : ', ['class' => 'col-sm-2 col-form-label'] ) }}
		<div class="col-md-10">
			{{ Form::textarea('remarks', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'rem', 'placeholder' => 'Remarks', 'autocomplete' => 'off', 'cols' => '120', 'rows' => '3']) }}
		</div>
	</div>

	<div class="form-group row mb-3 g-3 p-2">
		<div class="col-sm-10 offset-sm-2">
			{!! Form::button('Add Data', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
		</div>
	</div>
	{{ Form::close() }}

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#loc').select2({
	placeholder: 'Please choose',
	allowClear: true,
	closeOnSelect: true,
	width: '100%',
});

/////////////////////////////////////////////////////////////////////////////////////////
//date
$('#from').datetimepicker({
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
})
.on("dp.change dp.show dp.update", function (e) {
	var minDate = $('#from').val();
	$('#to').datetimepicker('minDate', minDate);
	$('#form').bootstrapValidator('revalidateField', 'date_from');
});


$('#to').datetimepicker({
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
	// useCurrent: false //Important! See issue #1075
})
.on("dp.change dp.show dp.update", function (e) {
	var maxDate = $('#to').val();
	$('#from').datetimepicker('maxDate', maxDate);
	$('#form').bootstrapValidator('revalidateField', 'date_to');
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
		'staff_id[]': {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
		'date_from': {
			validators: {
				notEmpty: {
					message: 'Please insert date start. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date start. '
				},
			}
		},
		'date_to': {
			validators: {
				notEmpty: {
					message: 'Please insert date end. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date end. '
				},
			}
		},
	}
});
@endsection
