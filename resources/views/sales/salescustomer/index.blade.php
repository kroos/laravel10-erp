@extends('layouts.app')

@section('content')
<style>
  div,
  table,
  tr,
  td {
    border: 1px solid black;
  }
</style>

<?php
$no = 1;
?>

<div class="container">
  @include('sales.salesdept.navhr')

  <div class="row mt-3">
    <div class="col-md-2">
      <h4>Customer</h4>
    </div>
    <div class="col-md-10">
      <a href="{{ route('salescustomer.create') }}" class="btn btn-sm btn-outline-secondary">
        Add Customer
      </a>
    </div>
  </div>

  <div class="mt-3">
    <table id="customer" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th class="text-center" style="width: 20px;">ID</th>
          <th class="text-center" style="width: 200px;">Customer</th>
          <th class="text-center" style="width: 150px;">Contact</th>
          <th class="text-center">Address</th>
          <th class="text-center" style="width: 100px;">Phone No</th>


          <th class="text-center" style="width: 40px;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($customers as $customer)
        <tr>
          <td class="text-center">
            {{ $no++ }}
          </td>
          <td>
  
            <input type="text" readonly value="{{ $customer->customer }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <td class="text-center">

          </td>
          <td class="text-center">

          </td>
          <td class="text-center">

          </td>
          <td class="text-center">

          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#customer').DataTable({
"paging": true,
"lengthMenu": [ [25,50,100,-1], [25,50,100,"All"] ],
"order": [ 1, 'asc' ],
responsive: true
});

$(function () {
$('[data-toggle="tooltip"]').tooltip()
});
@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection