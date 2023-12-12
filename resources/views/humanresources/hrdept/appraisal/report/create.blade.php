@extends('layouts.app')
@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<div class="col-sm-12 row">
		{{ Form::open(['route' => ['appraisalexcelreport.store'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}

		<div class="form-group row m-2 {{ $errors->has('year') ? 'has-error' : '' }}">
			{{ Form::label('year', 'Appraisal Report Year :', ['class' => 'col-sm-4 col-form-label']) }}
			<div class="col-sm-8">
				<input name="year" id="year" type="text" class="form-control form-control-sm col-sm-8" placeholder="Year" />
			</div>
		</div>

		<div class="form-group row m-3">
			<div class="col-sm-8 offset-sm-4">
				{!! Form::button('Appraisal Report', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
			</div>
		</div>
		{{ Form::close() }}
	</div>
</div>

@endsection
@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection

