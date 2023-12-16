@extends('layouts.app')

@section('content')
<div class="col-sm-12 row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Add Staff For Outstation Attendance</h4>
	<div class="col-sm-12 row">
		{!! Form::model($hroutstationattendance, ['route' => ['hroutstationattendance.update', $hroutstationattendance->id], 'method' => 'PATCH', 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}

		<div class="form-group row m-3 {{ $errors->has('date_attend') ? 'has-error' : Null }}">
			{{ Form::label('date', 'Attend Date : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8" style="position:relative;">
				{{ Form::text('date_attend', @$value, ['class' => 'form-control form-control-sm col-sm-auto', 'id' => 'date', 'readonly']) }}
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
				<select name="staff_id[]" id="staff" class="form-select form-select-sm col-sm-5"></select>
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('in') ? 'has-error' : Null }}">
			{{ Form::label('in', 'In : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8" style="position:relative;">
				{{ Form::text('in', @$value, ['class' => 'form-control form-control-sm col-sm-auto', 'id' => 'in']) }}
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('out') ? 'has-error' : Null }}">
			{{ Form::label('out', 'Out : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8" style="position:relative;">
				{{ Form::text('out', @$value, ['class' => 'form-control form-control-sm col-sm-auto', 'id' => 'out']) }}
			</div>
		</div>

		<div class="form-group row m-3 {{ $errors->has('in') ? 'has-error' : Null }}">
			{{ Form::label('remarks', 'Remarks : ', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8">
				{{ Form::textarea('remarks', @$value, ['class' => 'form-control form-control-sm col-sm-auto', 'id' => 'remarks']) }}
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
});

// $('document').ready();
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
		},
		transport: function (params, success, failure) {
			var $request = $.ajax(params);

			$request.then(success);
			$request.fail(failure);
			console.log($request);
			// return $request;
		},
	},
	allowClear: true,
	closeOnSelect: true,
});


	// Fetch the preselected item, and add to the control
	var location = $('#loc');
	$.ajax({
		url: "{{ route('outstationattendancelocation') }}",
		type: "POST",
		data: {
				// id: $('#id').val(),
				 _token: '{{ csrf_token() }}',
				 date_attend: $('#date').val(),
		},
		dataType: 'json',
		global: false,
		async:false,
		done: (function(response) {
			// you will get response from your php page (what you echo or print)
			console.log(response);
			return response;
		}),
		fail: (function(jqXHR, textStatus, errorThrown) {
			alert( "error" );
			console.log(textStatus, errorThrown);
		}),
		always: (function() {
			// alert( "complete" );
		})
//	})
//	.then(function (data) {
//		console.log(data.results);
//		// create the option and append to Select2
//		var option = new Option(data.text, data.id, true, true);
//		location.append(option).trigger('change');

//		// manually trigger the `select2:select` event
//		location.trigger({
//			type: 'select2:select',
//			params: {
//				data: data
//			}
//		});
	});

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
$('#staff').val('{{ $hroutstationattendance->staff_id }}').trigger('change');


/////////////////////////////////////////////////////////////////////////////////////////
$('#in, #out').datetimepicker({
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
	format: 'h:mm A',
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
		'date_attend': {
			validators: {
				notEmpty: {
					message: 'Please insert date. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'Please insert date. '
				},
			}
		},
		'outstation_id': {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
	}
});
@endsection
