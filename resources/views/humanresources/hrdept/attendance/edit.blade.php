@extends('layouts.app')

@section('content')

<!-- <style>
  div {
    border: 1px solid black;
  }
</style> -->

<?php
$staff = $attendance->belongstostaff()->get()->first();
?>

<div class="col-12">
  <div class="d-flex justify-content-center align-items-center">
    <div class="col-md-7">

      {!! Form::model($attendance, ['route' => ['attendance.update', $attendance->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}

      <h5>Attendance Edit</h5>

      <div class="row mt-3"></div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'id', 'ID', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$staff->id, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'name', 'NAME', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'date', 'DATE', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'day_type', 'DAY TYPE', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'attendance_type', 'CAUSE', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'in', 'IN', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'break', 'BREAK', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'resume', 'RESUME', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'out', 'OUT', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'duration', 'DURATION', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'overtime', 'OVERTIME', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'remark', 'REMARK', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'hr_remark', 'HR REMARK', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3">
          {!! Form::label( 'exception', 'EXCEPTION', ['class' => 'form-control border-0'] ) !!}
        </div>
        <div class="col-md-9 {{ $errors->has('mobile') ? 'has-error' : '' }}">
          {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert'] ) !!}
        </div>
      </div>

      {{ Form::close() }}

    </div>
  </div>
</div>
@endsection


@section('js')

@endsection