@extends('layouts.app')

@section('content')
<div class="container">
  @include('humanresources.hrdept.navhr')

  <div class="row mt-3"></div>

  <h4>Attendance Upload</h4>

  {{ Form::open(['route' => ['attendanceupload.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('softcopy', 'Excel File')}}
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
// VALIDATOR
$(document).ready(function() {
$('#form').bootstrapValidator({
feedbackIcons: {
valid: '',
invalid: '',
validating: ''
},

fields: {
softcopy: {
validators: {
file: {
extension: 'xls,xlsx', // no space
type: 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // no space
message: 'The selected file is not valid. Please use xls or xlsx file format.'
},
}
},

}
})
});
@endsection