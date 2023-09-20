@extends('layouts.app')

@section('content')
<style>
  /* div {
    border: 1px solid black;
  } */

  p {
    border: 1px solid black;
  }
</style>

<style>
  .scrollable-div {
    width: 100%;
    height: 400px;
    /* Set the height as needed */
    overflow: auto;
    /* Add scrollbars when content overflows */
    border: 1px solid #ccc;
    /* Optional border for visualization */
  }

  /* Optional styling for the content inside the scrollable div */
  .scrollable-content {
    padding: 10px;
  }
</style>

<?php
use App\Models\Staff;
use App\Models\Customer;

$staffs = Staff::where('active', 1)->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Add Replacement Leave</h4>

  {{ Form::open(['route' => ['rleave.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('name', 'Name')}}
    </div>
    <div class="col-md-10">
      {{Form::label('name', 'Name')}}
      <div class="scrollable-div">
        <div class="scrollable-content">
          @foreach ($staffs as $staff)
          <p>
            <input class="form-check-input" type="checkbox" value="" id="checkAll" checked>
            <label class="form-check-label" for="checkAll">{{ $staff->name }}</label>

</p>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date_start', 'Date Start')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('date_start', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_start', 'placeholder' => 'Date Start', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('date_end', 'Date End')}}
    </div>
    <div class="col-md-10">
      {{ Form::text('date_end', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'date_end', 'placeholder' => 'Date End', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('customer_id', 'Customer')}}
    </div>
    <div class="col-md-10">
      {{Form::select('customer_id', Customer::pluck('customer', 'id')->toArray(), @$value, ['class' => 'form-control customer_id', 'id' => 'customer_id', 'placeholder' => ''])}}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      {{Form::label('reason', 'Reason')}}
    </div>
    <div class="col-md-10">
      {!! Form::text( 'reason', @$value, ['class' => 'form-control', 'id' => 'reason', 'placeholder' => 'Please Insert'] ) !!}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      {!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {!! Form::close() !!}

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// MULTIPLE SELECTION
$("#staff_id").select2({
maximumSelectionLength: 100,
placeholder: '',
allowClear: true,
width: '100%',
});


/////////////////////////////////////////////////////////////////////////////////////////
$('#customer_id').select2({
placeholder: '',
width: '100%',
allowClear: true,
closeOnSelect: true,
});


/////////////////////////////////////////////////////////////////////////////////////////
// DATE PICKER
$('#date_start, #date_end').datetimepicker({
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
@endsection

@section('nonjquery')

@endsection