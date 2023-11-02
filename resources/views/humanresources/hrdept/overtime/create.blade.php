<?php
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HROvertimeRange;
?>
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

<div class="container justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-start">Add Overtime Staff</h4>
	{{ Form::open(['route' => ['overtime.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

	<div class="row justify-content-center">
		<div class="col-sm-6 gy-1 gx-1 align-items-start">

			<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
				{{ Form::label( 'rel', 'Staff : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-sm-8 scrollable-div">
					@if(Staff::where('active', 1)->count())
						<?php $i = 1 ?>
						@foreach(Staff::where('active', 1)->get() as $k)
							<div class="form-check mb-1 g-3">
								<input class="form-check-input" name="staff_id[]" type="checkbox" value="{{ $k->id }}" id="staff_{{ $i }}">
								<label class="form-check-label" for="staff_{{ $i }}">{{ Login::where([['staff_id', $k->id], ['active', 1]])->first()?->username }} - {{ $k->name }}</label>
							</div>
							<?php $i++ ?>
						@endforeach
					@endif

				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
				{{ Form::label( 'nam', 'Date Overtime : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-auto" style="position: relative;">
					{{ Form::text('ot_date', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'nam', 'placeholder' => 'Date Overtime', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group row mb-3 {{ $errors->has('overtime_range_id') ? 'has-error' : '' }}">
				{{ Form::label( 'mar', 'Overtime : ', ['class' => 'col-sm-4 col-form-label'] ) }}
				<div class="col-sm-8">
					<select name="overtime_range_id" id="mar" class="form-select form-select-sm col-sm-8" placeholder="Please Select"></select>
				</div>
			</div>

		</div>
	</div>

	<div class="offset-5 mb-6">
		{!! Form::submit('Add Overtime Staff', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
	</div>

	{{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#rel').select2({
	placeholder: 'Please Select',
	width: '100%',
	ajax: {
		url: '{{ route('samelocationstaff') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				id: {{ \Auth::user()->belongstostaff->id }},
				_token: '{!! csrf_token() !!}',
				search: params.term,
			}
			return query;
		}
	},
	allowClear: true,
	closeOnSelect: true,
});

$('#mar').select2({
	placeholder: 'Please Select',
	width: '100%',
	ajax: {
		url: '{{ route('overtimerange') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				id: {{ \Auth::user()->belongstostaff->id }},
				_token: '{!! csrf_token() !!}',
				search: params.term,
			}
			return query;
		}
	},
	allowClear: true,
	closeOnSelect: true,
});

/////////////////////////////////////////////////////////////////////////////////////////
<?php
$mi = \Auth::user()->belongstostaff;

// position
$pos = $mi->div_id;

// dept HR
$dept = $mi->belongstomanydepartment()->where('main', 1)->first();
?>

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
	@if( $pos == 4 || ($pos == 1 && $dept->department_id == 21) )
		minDate: moment().format(),
	@endif
}).on("dp.change", function (e) {
	$('#form').bootstrapValidator('revalidateField', 'ot_date');
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
					message: 'Please insert date. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'The value is not a valid date ',
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
