@extends('layouts.app')

@section('content')
{!! Form::open(['route' => ['password.store'], 'class' => 'needs-validation','id' => 'form', 'autocomplete' => 'off', 'files' => true]) !!}
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<?php
$pass = App\Models\Staff::firstwhere('email', $request->email)->hasmanylogin()->firstWhere('active', 1)->username;
?>
<div class="mb-3 row">
    <div class="form-group row {{ $errors->has('username') ? 'has-error' : '' }}">
        {!! Form::label('username', 'Username : ', ['class' => 'col-sm-2 col-form-label col-form-label-sm']) !!}
        <div class="col-sm-10">
            {{ Form::text('username', old('username', $pass), ['class' => 'form-control form-control-sm col-auto', 'id' => 'username', 'placeholder' => 'Username']) }}
        </div>
    </div>
</div>
<div class="mb-3 row">
    <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}">
        {!! Form::label('password', 'Password : ', ['class' => 'col-sm-2 col-form-label col-form-label-sm']) !!}
        <div class="col-sm-10">
            {{ Form::password('password', ['class' => 'form-control form-control-sm col-auto', 'id' => 'password', 'placeholder' => 'Password']) }}
        </div>
    </div>
</div>
<div class=" row">
    <div class="form-group row {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
        {!! Form::label('password_confirmation', 'Confirm Password : ', ['class' => 'col-sm-2 col-form-label col-form-label-sm']) !!}
        <div class="col-sm-10">
            {{ Form::password('password_confirmation', ['class' => 'form-control form-control-sm col-auto', 'id' => 'password_confirmation', 'placeholder' => 'Confirm Password']) }}
        </div>
    </div>
</div>


{!! Form::submit('Reset Password', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
{!! Form::close(); !!}
@endsection

@section('js')
@endsection
