@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;
use App\Models\Login;
$s = Staff::where('active', 1)->get();
foreach ($s as $v) {
		$ls[$v->id] = Login::where([['active', 1], ['staff_id', $v->id]])->first()?->username.'  '.$v->name;
}
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Add Remarks Attendance</h4>
	{!! Form::model($attendanceremark, ['route' => ['attendanceremark.update', $attendanceremark->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}

	<div class="form-group row m-3 {{ $errors->has('staff_id') ? 'has-error' : NULL }}">
		{{Form::label('staff', 'Staff : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			{{ Form::select('staff_id', $ls, @$value, ['id' => 'staff', 'class' => 'form-select form-select-sm', 'placeholder' => 'Please choose']) }}
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('date_from') ? 'has-error' : NULL }}">
		{{Form::label('from', 'From : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8" style="position: relative;">
			{{ Form::text('date_from', @$value, ['id' => 'from', 'class' => 'form-control form-control-sm', 'placeholder' => 'Date From']) }}
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('date_to') ? 'has-error' : NULL }}">
		{{Form::label('to', 'To : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8" style="position: relative;">
			{{ Form::text('date_to', @$value, ['id' => 'to', 'class' => 'form-control form-control-sm', 'placeholder' => 'Date To']) }}
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('attendance_remarks') ? 'has-error' : NULL }}">
		{{Form::label('ar', 'Attendance Remarks : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			{{ Form::textarea('attendance_remarks', @$value, ['id' => 'ar', 'class' => 'form-control form-control-sm', 'placeholder' => 'Attendance Remarks']) }}
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('hr_attendance_remarks') ? 'has-error' : NULL }}">
		{{Form::label('hrar', 'HR Attendance Remarks : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			{{ Form::textarea('hr_attendance_remarks', @$value, ['id' => 'hrar', 'class' => 'form-control form-control-sm', 'placeholder' => 'HR Attendance Remarks']) }}
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('remarks') ? 'has-error' : NULL }}">
		{{Form::label('rem', 'Remarks : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			{{ Form::textarea('remarks', @$value, ['id' => 'rem', 'class' => 'form-control form-control-sm', 'placeholder' => 'Remarks']) }}
		</div>
	</div>

	<div class="col-sm-8 offset-sm-4">
		{!! Form::button('Update Remarks', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
	</div>
	{{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#staff').select2({
	placeholder: 'Please choose',
	allowClear: true,
	closeOnSelect: true,
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
	$('#to').datetimepicker('minDate', $('#from').val());
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
	$('#from').datetimepicker('maxDate', $('#to').val());
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
		'staff_id': {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
		'date_from': {
			validators: {
				notEmpty: {
					message: 'Please insert date from. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date from. '
				},
			}
		},
		'date_to': {
			validators: {
				notEmpty: {
					message: 'Please insert date to. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date to. '
				},
			}
		},
	}
});
@endsection
