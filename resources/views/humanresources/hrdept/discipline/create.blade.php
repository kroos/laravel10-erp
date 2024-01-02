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

$staff = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->select(DB::raw('CONCAT(username, " - ", name) AS display_name'), 'staffs.id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->pluck('display_name', 'id')
  ->toArray();

$disciplinary_action = OptDisciplinaryAction::pluck('disciplinary_action', 'id')->toArray();
$violation = OptViolation::select(DB::raw('CONCAT(IFNULL(violation, ""), " - ", IFNULL(remarks, "")) AS display_violation'), 'id')->pluck('display_violation', 'id')->toArray();
$infraction = OptInfractions::select(DB::raw('CONCAT(IFNULL(infraction, ""), " - ", IFNULL(remarks, "")) AS display_infraction'), 'id')->pluck('display_infraction', 'id')->toArray();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Add Discipline</h4>

  {{ Form::open(['route' => ['discipline.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
      {{ Form::select('staff_id', $staff, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'staff_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('supervisor', 'Supervisor Incharge')}}
    </div>
    <div class="col-md-10 {{ $errors->has('supervisor_id') ? 'has-error' : '' }}">
      {{ Form::select('supervisor_id', $staff, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'supervisor_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('disciplinary_action', 'Disciplinary Action')}}
    </div>
    <div class="col-md-10 {{ $errors->has('disciplinary_action_id') ? 'has-error' : '' }}">
      {{ Form::select('disciplinary_action_id', $disciplinary_action, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'disciplinary_action_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('violation', 'Violation')}}
    </div>
    <div class="col-md-10 {{ $errors->has('violation_id') ? 'has-error' : '' }}">
      {{ Form::select('violation_id', $violation, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'violation_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('infraction', 'Infraction Level')}}
    </div>
    <div class="col-md-10 {{ $errors->has('infraction_id') ? 'has-error' : '' }}">
      {{ Form::select('infraction_id', $infraction, @$value, ['class' => 'form-control form-select form-select-sm col-auto', 'id' => 'infraction_id', 'placeholder' => '', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('misconduct date', 'Misconduct Date')}}
    </div>
    <div class="col-md-10 {{ $errors->has('misconduct_date') ? 'has-error' : '' }}">
      {{ Form::text('misconduct_date', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'misconduct_date', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('action taken date', 'Action Taken Date')}}
    </div>
    <div class="col-md-10 {{ $errors->has('action_taken_date') ? 'has-error' : '' }}">
      {{ Form::text('action_taken_date', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'action_taken_date', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('reason', 'Description of Incident')}}
    </div>
    <div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
      {!! Form::textarea('reason', @$value, ['class' => 'form-control', 'id' => 'reason', 'placeholder' => 'Please Insert Incident Description'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('action to be taken', 'Action to be Taken')}}
    </div>
    <div class="col-md-10 {{ $errors->has('reason') ? 'has-error' : '' }}">
      {!! Form::textarea('action_to_be_taken', @$value, ['class' => 'form-control', 'id' => 'action_to_be_taken', 'placeholder' => 'Please Insert Action Taken'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('softcopy', 'Softcopy')}}
    </div>
    <div class="col-md-10">
      {!! Form::file('softcopy', ['class' => 'form-control', 'id' => 'softcopy']) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      {!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {!! Form::close() !!}

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
/////////////////////////////////////////////////////////////////////////////////////////
$('.form-select').select2({
placeholder: '',
width: '100%',
allowClear: true,
closeOnSelect: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#misconduct_date, #action_taken_date').datetimepicker({
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
});


/////////////////////////////////////////////////////////////////////////////////////////
// VALIDATOR
$(document).ready(function() {
$('#form').bootstrapValidator({
feedbackIcons: {
valid: '',
invalid: '',
validating: ''
},

fields: {
staff_id: {
validators: {
notEmpty: {
message: 'Please select staff.'
}
}
},

supervisor_id: {
validators: {
notEmpty: {
message: 'Please select supervisor incharge.'
}
}
},

disciplinary_action_id: {
validators: {
notEmpty: {
message: 'Please select disciplinary action.'
}
}
},

violation_id: {
validators: {
notEmpty: {
message: 'Please select violation.'
}
}
},

infraction_id: {
validators: {
notEmpty: {
message: 'Please select infraction.'
}
}
},

misconduct_date: {
validators: {
notEmpty: {
message: 'Please insert misconduct date.'
}
}
},

action_taken_date: {
validators: {
notEmpty: {
message: 'Please insert action taken date.'
}
}
},

reason: {
validators: {
notEmpty: {
message: 'Please insert incident description.'
}
}
},

action_to_be_taken: {
validators: {
notEmpty: {
message: 'Please insert action to be taken.'
}
}
},

softcopy: {
validators: {
file: {
extension: 'jpeg,jpg,png,bmp,pdf,doc,docx', // no space
type: 'image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', // no space
maxSize: 5242880, // 5120 * 1024,
message: 'The selected file is not valid. Please use jpeg, jpg, png, bmp, pdf or doc and the file is below than 5MB.'
},
}
},

}
})
});
@endsection

@section('nonjquery')

@endsection