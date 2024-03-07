@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h2>Report of Staff Checking Incentive</h2>

	<div class="hstack align-items-start justify-content-between">
		<div class="col-sm-12">
			{{ Form::open(['route' => ['cicategorystaffcheckreport.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) }}

			<div class="form-group hstack @error('week_id') has-error is-invalid @enderror">
				{{ Form::label( 'week1', 'From Week : ', ['class' => 'col-sm-2 col-form-label'] ) }}
				<div class="col-sm-4 align-items-center">
					{{ Form::select('week_id', [], @$value, ['class' => 'form-select form-select-sm', 'id' => 'week1', 'placeholder' => 'Please choose', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="form-group hstack @error('week_id') has-error is-invalid @enderror">
				{{ Form::label( 'week2', 'To Week : ', ['class' => 'col-sm-2 col-form-label'] ) }}
				<div class="col-sm-4 align-items-center">
					{{ Form::select('week_id', [], @$value, ['class' => 'form-select form-select-sm', 'id' => 'week2', 'placeholder' => 'Please choose', 'autocomplete' => 'off']) }}
				</div>
			</div>

			<div class="offset-sm-2 col-sm-auto mt-3 ">
				<button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
			</div>

			{{ Form::close() }}
		</div>
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#week1,#week2').select2({
	placeholder: 'Please choose',
	allowClear: true,
	closeOnSelect: true,
	width: '100%',
	ajax: {
		url: '{{ route('week_dates') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				_token: '{!! csrf_token() !!}',
				search: params.term,
			}
			return query;
		}
	},
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
		'week_id': {
			validators: {
				notEmpty: {
					message: 'Please choose '
				},
			}
		},
	}
});
@endsection
