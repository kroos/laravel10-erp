@extends('layouts.app')
@section('content')
<div class="mb-3 row text-sm">
    {{ __('Forgot your password? No problem. Just let us know your username and we will email you a password reset link that will allow you to choose a new one.') }}
</div>
{{ Form::open(['route' => ['password.email'], 'id' => 'form', 'autocomplete' => 'off', 'files' => true]) }}
<div class="mb-3 row">
    <div class="form-group row {{ $errors->has('username') ? 'has-error' : '' }}">
        {!! Form::label('username', 'Username : ', ['class' => 'col-sm-2 col-form-label col-form-label-sm']) !!}
        <div class="col-sm-10">
            {{ Form::text('username', @$value, ['class' => 'form-control form-control-sm', 'id' => 'username', 'placeholder' => 'Username']) }}
        </div>
    </div>
</div>
{!! Form::submit('Email Password Reset Link', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
{{ Form::close() }}
@endsection
@section('js')
@endsection