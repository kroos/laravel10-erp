@extends('layouts.app')


@section('content')

<style>
  /* table,
  tr,
  td,
  div {
    border: 1px solid black;
  } */

</style>

<?php

use \App\Models\Staff;
use \App\Models\HumanResources\OptAppraisalCategories;

$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->select('logins.username', 'staffs.name', 'staffs.id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->orderBy('logins.username', 'ASC')
  ->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <div class="row mt-3">
    <div class="col-md-2">
      <h4>Appraisal List</h4>
    </div>
    <div class="col-md-10">
  
    </div>
  </div>

  <div class="row">&nbsp;</div>

  <div>
    <table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th class="text-center" style="max-width: 30px;">ID</th>
          <th class="text-center">Name</th>
          <th class="text-center" style="max-width: 80px;">Location</th>
          <th class="text-center" style="max-width: 100px;">Department</th>
          <th class="text-center" style="max-width: 150px;">Evaluator1</th>
          <th class="text-center" style="max-width: 150px;">Evaluator2</th>
          <th class="text-center" style="max-width: 150px;">Evaluator3</th>
          <th class="text-center" style="max-width: 150px;">Evaluator4</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($staffs as $staff)

        <?php
        $markers = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
          ->join('pivot_apoint_appraisals', 'staffs.id', '=', 'evaluator_id')
          ->select('staffs.name')
          ->where('staffs.active', 1)
          ->where('logins.active', 1)
          ->whereNull('pivot_apoint_appraisals.deleted_at')
          ->where('pivot_apoint_appraisals.evaluatee_id', $staff->id)
          ->orderBy('logins.username', 'ASC')
          ->get();
        ?>

        <tr>
          <td class="text-center">
            {{ $staff->username }}
          </td>
          <td data-toggle="tooltip" title="{{ $staff->name }}">
            <input type="text" readonly value="{{ $staff->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <td class="text-center">

          </td>
          <td class="text-center">

          </td>

          @foreach ($markers as $marker)
          <td data-toggle="tooltip" title="{{ $marker->name }}">
            <input type="text" readonly value="{{ $marker->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          @endforeach

          @for ($a=count($markers); $a<'4'; $a++)
          <td></td>
          @endfor
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
$('#staff').DataTable({
  "paging": false,
  "order": [ 0, 'asc' ],
  responsive: true
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
});


////////////////////////////////////////////////////////////////////////////////////
// DISTRIBUTE APPRAISAL
$(document).on('click', '.distribute', function(e){

  e.preventDefault();
  swal.fire({
    title: 'DISTRIBUTE',
    text: "Do you want to distribute current year appraisal?",
    icon: 'info',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    showLoaderOnConfirm: true,

    preConfirm: function() {
      return new Promise(function(resolve) {
        $.ajax({
          type: 'PATCH',
          url: '{{ url('appraisallist/update') }}',
          data: {
            _token : $('meta[name=csrf-token]').attr('content'),
          },
          dataType: 'json'
        })
        .done(function(response){
          swal.fire('Distributed', response.message, response.status)
          .then(function(){
            window.location.reload(true);
          });
        })
        .fail(function(){
          swal.fire('Error', 'Something wrong with ajax!', 'error');
        })
      });
    },
    allowOutsideClick: false
  })
  .then((result) => {
    if (result.dismiss === swal.DismissReason.cancel) {
      swal.fire('Cancelled', 'Process has been cancelled', 'info')
    }
  });
});
@endsection