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
      <h4>Customer Details</h4>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-2">
      Customer
    </div>
    <div class="col-md-10">
      {{ Form::text('customer', $customer->customer, ['class' => 'form-control form-control-sm', 'id' => 'customer', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Contact
    </div>
    <div class="col-md-10">
      {{ Form::text('customer', $customer->contact, ['class' => 'form-control form-control-sm', 'id' => 'customer', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Phone
    </div>
    <div class="col-md-10">
      {{ Form::text('customer', $customer->phone, ['class' => 'form-control form-control-sm', 'id' => 'customer', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Fax
    </div>
    <div class="col-md-10">
      {{ Form::text('customer', $customer->fax, ['class' => 'form-control form-control-sm', 'id' => 'customer', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Area
    </div>
    <div class="col-md-10">
      {{ Form::text('customer', $customer->area, ['class' => 'form-control form-control-sm', 'id' => 'customer', 'readonly' => 'readonly']) }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2">
      Address
    </div>
    <div class="col-md-10">
      {{ Form::textarea('address', $customer->address, ['class' => 'form-control form-control-sm', 'id' => 'address', 'readonly' => 'readonly', 'rows' => '3']) }}
    </div>
  </div>

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