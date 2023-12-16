@extends('layouts.app')

@section('content')
<div class="col-sm-12 row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation Attendance</h4>
	<div class="col-sm-12 row">
		{!! Form::open(['route' => ['hroutstationattendance.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}

		<div class="form-group row m-3 {{ $errors->has('date_attend') ? 'has-error' : Null }}">
			{{ Form::label('date', 'Attend Date : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8" style="position:relative;">
				{{ Form::text('date_attend', @$value, ['class' => 'form-control form-control-sm col-sm-auto', 'id' => 'date']) }}
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('attend_date') ? 'has-error' : Null }}">
			{{ Form::label('loc', 'Location : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8">
				<select name="outstation_id" id="loc" class="form-select form-select-sm col-sm-5"></select>
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('staff_id') ? 'has-error' : Null }}">
			{{ Form::label('staff', 'Staff : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8">
				<select name="staff_id" id="staff" class="form-select form-select-sm col-sm-5" multiple="multiple"></select>
			</div>
		</div>

		<div class="offset-sm-4 col-sm-8">
			{{ Form::submit('Generate Attendance',['class' => 'btn btn-sm btn-outline-secondary']) }}
		</div>

		{{ Form::close() }}
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
//date
$('#date').datetimepicker({
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
	useCurrent: true,
})
.on('dp.change dp.update', function(e) {
	// console.log(e);

	//enable select 2 for backup
	$('#loc').select2({
		placeholder: 'Please Choose',
		width: '100%',
		ajax: {
			url: '{{ route('outstationattendancelocation') }}',
			// data: { '_token': '{!! csrf_token() !!}' },
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					_token: '{!! csrf_token() !!}',
					date_attend: $('#date').val(),
					search: params.term,
					type: 'public'
				}
				return query;
			}
		},
		allowClear: true,
		closeOnSelect: true,
	});

	// get staff
	$('#loc').on('change, select2:select', function (e) {
		// console.log($('#loc').val());

		$('#staff').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('outstationattendancestaff') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						_token: '{!! csrf_token() !!}',
						outstation_id: $('#loc').val(),
						date_attend: $('#date').val(),
						search: params.term,
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});



	});




});

/////////////////////////////////////////////////////////////////////////////////////////
//select 2

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
		'date_attend': {
			validators: {
				notEmpty: {
					message: 'Please insert date. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date start. '
				},
			}
		},
	}
});
@endsection
