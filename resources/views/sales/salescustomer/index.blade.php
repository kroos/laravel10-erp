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
          <th class="text-center" style="width: 120px;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($customers as $customer)
        <tr>
          <td class="text-center">
            {{ $no++ }}
          </td>
          <td data-toggle="tooltip" title="{{ $customer->customer }}">
            <input type="text" readonly value="{{ $customer->customer }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <td data-toggle="tooltip" title="{{ $customer->contact }}">
            <input type="text" readonly value="{{ $customer->contact }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <td data-toggle="tooltip" title="{{ $customer->address }}">
            <input type="text" readonly value="{{ $customer->address }}" style="border-style:none; outline:none; background-color:transparent; width:97%; height:100%;" />
          </td>
          <td class="text-center" data-toggle="tooltip" title="{{ $customer->phone }}">
            <input type="text" readonly value="{{ $customer->phone }}" style="border-style:none; outline:none; background-color:transparent; width:97%; height:100%;" />
          </td>
          <td class="text-center">
            <a href="{{ route('salescustomer.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" title="View">
              <i class="bi bi-file-earmark-text" style="font-size: 15px;"></i>
            </a>
            &nbsp;
            <a href="{{ route('salescustomer.edit', $customer->id) }}" class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" title="Edit">
              <i class="bi bi-pencil-square" style="font-size: 15px;"></i>
            </a>
            &nbsp;
            <button type="button" class="btn btn-sm btn-outline-secondary customer_delete" data-id="{{ $customer->id }}">
              <i class="bi bi-trash3-fill" aria-hidden="true" style="font-size: 15px;"></i>
            </button>
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


////////////////////////////////////////////////////////////////////////////////////
// DELETE CUSTOMER
$(document).on('click', '.customer_delete', function(e){
  var customerId = $(this).data('id');
  SwalCustomerDelete(customerId);
  e.preventDefault();
});

function SwalCustomerDelete(customerId){
  swal.fire({
    title: 'DELETE',
    text: "Do you want to deletet the customer?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    showLoaderOnConfirm: true,

    preConfirm: function() {
      return new Promise(function(resolve) {
        $.ajax({
          type: 'DELETE',
          url: '{{ url('salescustomer') }}' + '/' + customerId,
          data: {
              _token : $('meta[name=csrf-token]').attr('content'),
              id: customerId,
          },
          dataType: 'json'
        })
        .done(function(response){
          swal.fire('Deleted', response.message, response.status)
          .then(function(){
            window.location.reload(true);
          });
        })
        .fail(function(){
          swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
        })
      });
    },
    allowOutsideClick: false
  })
  .then((result) => {
    if (result.dismiss === swal.DismissReason.cancel) {
      swal.fire('Cancelled', 'Delete has been cancelled', 'info')
    }
  });
}
@endsection

@section('nonjquery')
@endsection