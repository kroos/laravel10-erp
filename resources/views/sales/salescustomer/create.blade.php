@extends('layouts.app')

@section('content')
<style>
  /* div,
  table,
  tr,
  td {
    border: 1px solid black;
  } */
</style>

<?php
$no = 1;
?>

<div class="container">
  @include('sales.salesdept.navhr')

  <div class="row mt-3">
    <div class="col-md-2">
      <h4>Add Customer</h4>
    </div>
  </div>

  {{ Form::open(['route' => ['salescustomer.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="row mt-4">
    <div class="col-md-2">
      Customer
    </div>
    <div class="col-md-10">
      {{ Form::text('customer', @$value, ['class' => 'form-control form-control-sm', 'id' => 'customer', 'placeholder' => 'Customer', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Contact
    </div>
    <div class="col-md-10">
      {{ Form::text('contact', @$value, ['class' => 'form-control form-control-sm', 'id' => 'contact', 'placeholder' => 'Contact', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Phone
    </div>
    <div class="col-md-10">
      {{ Form::text('phone', @$value, ['class' => 'form-control form-control-sm', 'id' => 'phone', 'placeholder' => 'Phone', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Fax
    </div>
    <div class="col-md-10">
      {{ Form::text('fax', @$value, ['class' => 'form-control form-control-sm', 'id' => 'fax', 'placeholder' => 'Fax', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Area
    </div>
    <div class="col-md-10">
      {{ Form::text('area', @$value, ['class' => 'form-control form-control-sm', 'id' => 'area', 'placeholder' => 'Area', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Address
    </div>
    <div class="col-md-10">
      {{ Form::textarea('address', @$value, ['class' => 'form-control form-control-sm', 'id' => 'address', 'placeholder' => 'Address', 'rows' => '3']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      {!! Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {{ Form::close() }}

  <div class="row mt-3">
    <div class="col-md-12 text-center">
      <a href="">
        <button onclick="goBack()" class="btn btn-sm btn-outline-secondary" id="back">
          Back
        </button>
      </a>
    </div>
  </div>

</div>

@endsection

@section('js')
@endsection

@section('nonjquery')
function goBack() {
  window.history.back();
}
@endsection