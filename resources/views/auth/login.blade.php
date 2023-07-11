@extends('layouts.app')

@section('content')

{!! Form::open(['route' => ['login'], 'class' => 'needs-validation','id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}
<div class="mb-3 row">
    <div class="form-group row {{ $errors->has('username') ? 'has-error' : '' }}">
        {!! Form::label('username', 'Username : ', ['class' => 'col-sm-2 col-form-label col-form-label-sm']) !!}
        <div class="col-sm-10">
            {{ Form::text('username', @$value, ['class' => 'form-control form-control-sm', 'id' => 'username', 'placeholder' => 'Username']) }}
        </div>
    </div>
</div>
<div class="mb-3 row">
    <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}">
        {!! Form::label('password', 'Password : ', ['class' => 'col-sm-2 col-form-label col-form-label-sm']) !!}
        <div class="col-sm-10">
            {{ Form::password('password', ['class' => 'form-control form-control-sm', 'id' => 'password', 'placeholder' => 'Password']) }}
        </div>
    </div>
</div>
<div class="mb-3 row">
    <div class="pretty p-svg p-round p-plain p-jelly">
        {{ Form::checkbox('remember', @$value, false, ['class' => 'form-check-input form-check-input-sm', 'id' => 'remember_me']) }}
        <div class="state p-success">
            <span class="svg"><i class="bi bi-check"></i></span>
            <label for="remember_me">{{ __('Remember me') }}</label>
        </div>
    </div>
</div>
{!! Form::submit('Login', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
{!! Form::close(); !!}

<div class="flex items-center justify-end mt-4">
    @if (Route::has('password.request'))
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('password.request') }}">
            {{ __('Forgot your password?') }}
        </a>
    @endif
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////





/////////////////////////////////////////////////////////////////////////////////////////
// validator
$(document).ready(function() {
	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: 'fa-light fa-check',
			invalid: 'fa-sharp fa-light fa-xmark',
			validating: 'fa-duotone fa-spinner-third'
		},
		fields: {
			username: {
				validators: {
					notEmpty: {
						message: 'Please insert username'
					},
				}
			},
			password: {
				validators: {
					notEmpty : {
						message: 'Please insert password'
					},
				}
			},
		}
	})
	.find('[name="reason"]')
	// .ckeditor()
	// .editor
		.on('change', function() {
			// Revalidate the bio field
		$('#form').bootstrapValidator('revalidateField', 'reason');
		// console.log($('#reason').val());
	})
	;
});

/////////////////////////////////////////////////////////////////////////////////////////
@endsection