@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Add Remarks Attendance</h4>
	{!! Form::open(['route' => ['attendanceremark.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}

	<div class="form-group row m-3 {{ $errors->has('staff_id') ? 'has-error' : NULL }}">
		{{Form::label('staff', 'Staff : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			<select name="staff_id" id="staff" class="form-select form-select-sm" value="{{ old('staff_id') }}"></select>
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('date_from') ? 'has-error' : NULL }}">
		{{Form::label('from', 'From : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8" style="position: relative;">
			<input type="text" name="date_from" id="from" class="form-control form-control-sm" value="{{ old('date_from') }}" placeholder="Date From">
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('date_to') ? 'has-error' : NULL }}">
		{{Form::label('to', 'To : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8" style="position: relative;">
			<input type="text" name="date_to" id="to" class="form-control form-control-sm" value="{{ old('date_to') }}" placeholder="Date To">
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('attendance_remarks') ? 'has-error' : NULL }}">
		{{Form::label('ar', 'Attendance Remarks : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			<textarea name="attendance_remarks" id="ar" class="form-control form-control-sm" value="{{ old('attendance_remarks') }}" placeholder="Attendance Remarks (Remark Display For All)"></textarea>
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('hr_attendance_remarks') ? 'has-error' : NULL }}">
		{{Form::label('hrar', 'HR Attendance Remarks : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			<textarea name="hr_attendance_remarks" id="hrar" class="form-control form-control-sm" value="{{ old('hr_attendance_remarks') }}" placeholder="HR Attendance Remarks (Remark Display Only For HR, Admin And Director)"></textarea>
		</div>
	</div>

	<div class="form-group row m-3 {{ $errors->has('remarks') ? 'has-error' : NULL }}">
		{{Form::label('rem', 'Remarks : ', ['class' => 'col-sm-4 form-label'])}}
		<div class="col-md-8">
			<textarea name="remarks" id="rem" class="form-control form-control-sm" value="{{ old('remarks') }}" placeholder="Remarks (Remark Database : Can Just Leave It Blank)"></textarea>
		</div>
	</div>

	<div class="col-sm-8 offset-sm-4">
		{!! Form::button('Add Remarks', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
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
	// width: '100%',
	ajax: {
		url: '{{ route('staffcrossbackup.staffcrossbackup') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var data = {
				_token: '{!! csrf_token() !!}',
				search: params.term,
				type: 'public'
			}
			return data;
		}
	},
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
