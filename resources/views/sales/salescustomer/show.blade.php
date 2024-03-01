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
    <div class="col-md-2" style="background-color: #e6e6e6">
      Customer
    </div>
    <div class="col-md-10">
      {{ $customer->customer }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2" style="background-color: #e6e6e6">
      Contact
    </div>
    <div class="col-md-10">
      {{ $customer->contact }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2" style="background-color: #e6e6e6">
      Phone
    </div>
    <div class="col-md-10">
      {{ $customer->phone }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2" style="background-color: #e6e6e6">
      Fax
    </div>
    <div class="col-md-10">
      {{ $customer->fax }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2" style="background-color: #e6e6e6">
      Area
    </div>
    <div class="col-md-10">
      {{ $customer->area }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-2" style="background-color: #e6e6e6">
      Address
    </div>
    <div class="col-md-10">
      {{ $customer->address }}
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12 text-center" >
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