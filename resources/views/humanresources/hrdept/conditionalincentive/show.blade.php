@extends('layouts.app')

@section('content')
<style>
  /* div {
    border: 1px solid black;
  } */
</style>

<?php

use App\Models\Staff;
use App\Models\HumanResources\OptDisciplinaryAction;
use App\Models\HumanResources\OptViolation;
use App\Models\HumanResources\OptInfractions;

$staff = $discipline->belongstostaff->name;
$supervisor = $discipline->belongstosupervisor->name;
$disciplinary_action = $discipline->belongstooptdisciplinaryaction->disciplinary_action;
$violation = $discipline->belongstooptviolation()->select('violation', 'remarks')->first();
$infraction = $discipline->belongstooptinfractions()->select('infraction', 'remarks')->first();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Show Discipline</h4>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('staff_id', $staff, ['class' => 'form-control form-select-sm', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('supervisor', 'Supervisor Incharge')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('supervisor_id', $supervisor, ['class' => 'form-control form-select-sm', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('disciplinary_action', 'Disciplinary Action')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('disciplinary_action_id', $disciplinary_action, ['class' => 'form-control form-control-sm', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('violation', 'Violation')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('violation_id', $violation->violation . ' - ' . $violation->remarks, ['class' => 'form-control form-control-sm', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('infraction', 'Infraction Level')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('infraction_id', $infraction->infraction . ' - ' . $infraction->remarks, ['class' => 'form-control form-control-sm', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('misconduct date', 'Misconduct Date')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('misconduct_date',  $discipline->misconduct_date, ['class' => 'form-control form-control-sm col-auto', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('action taken date', 'Action Taken Date')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('action_taken_date',  $discipline->action_taken_date, ['class' => 'form-control form-control-sm col-auto', 'readonly' => 'readonly']) }}
    </div>
  </div>






  <div class="row mt-3">
    <div class="col-md-2">
    {{Form::label('reason', 'Description of Incident')}}
    </div>
    <div class="col-md-10">
      {!! Form::textarea('reason', $discipline->reason, ['class' => 'form-control', 'readonly' => 'readonly'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
    {{Form::label('action to be taken', 'Action to be Taken')}}
    </div>
    <div class="col-md-10">
      {!! Form::textarea('action_to_be_taken', $discipline->action_to_be_taken, ['class' => 'form-control', 'readonly' => 'readonly'] ) !!}
    </div>
  </div>

  @if ($discipline->softcopy)
  <input type="hidden" name="old_softcopy" id="old_softcopy" value="{{ $discipline->softcopy }}">
  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('softcopy', 'Softcopy')}}
    </div>
    <div class="col-md-10">
      <a href="{{ asset('storage/disciplinary/' . $discipline->softcopy) }}" target="_blank">
        {{ $discipline->softcopy }}
      </a>
    </div>
  </div>
  @endif

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      <a href="{{ url()->previous() }}">
        <button class="btn btn-sm btn-outline-secondary">BACK</button>
      </a>
    </div>
  </div>

</div>
@endsection

@section('js')

@endsection

@section('nonjquery')

@endsection