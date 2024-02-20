@extends('layouts.app')


@section('content')

<style>
  /* div {
    border: 1px solid black;
  } */

  .scrollable-div-1 {
    /* Set the width height as needed */
    /*		width: 100%;*/
    height: 850px;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }

  .scrollable-div-2 {
    /* Set the width height as needed */
    /*		width: 100%;*/
    height: 800px;
    background-color: blanchedalmond;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }

  .hover:hover {
    background-color: #ffcc00;
  }

  .pivot_delete {
    background-color: transparent;
    border: none;
    padding: 0;
    margin: 0;
  }
</style>

<?php

use \App\Models\Staff;
use \App\Models\HumanResources\OptAppraisalCategories;

$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->leftjoin('option_appraisal_categories', 'staffs.appraisal_category_id', '=', 'option_appraisal_categories.id')
  ->select('logins.username', 'staffs.name', 'staffs.id', 'staffs.appraisal_category_id', 'option_appraisal_categories.category')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->orderBy('logins.username', 'ASC')
  ->get();

$appraisal_category = OptAppraisalCategories::orderBy('category', 'ASC')
  ->pluck('category', 'id')
  ->toArray();

$evaluator = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->select(DB::raw('CONCAT(username, " - ", name) AS display_name'), 'staffs.id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->orderBy('logins.username', 'ASC')
  ->pluck('display_name', 'id')
  ->toArray();

$evaluatees = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
  ->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=', 'pivot_dept_cate_branches.id')
  ->select('logins.username', 'staffs.*', 'pivot_dept_cate_branches.department')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->where('pivot_staff_pivotdepts.main', 1)
  ->orderBy('pivot_dept_cate_branches.department', 'ASC')
  ->orderBy('logins.username', 'ASC')
  ->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Apoint</h4>

  <div class="row">&nbsp;</div>

  <div class="row">
    <div class="col-6">

      <div class="row">
        <div class="scrollable-div-1">
          @foreach($staffs as $staff)

          <?php
          $markers = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
            ->join('pivot_apoint_appraisals', 'staffs.id', '=', 'evaluator_id')
            ->select('logins.username', 'staffs.name', 'pivot_apoint_appraisals.id')
            ->where('staffs.active', 1)
            ->where('logins.active', 1)
            ->whereNull('pivot_apoint_appraisals.deleted_at')
            ->where('pivot_apoint_appraisals.evaluatee_id', $staff->id)
            ->orderBy('logins.username', 'ASC')
            ->get();
          ?>

          <div class="row hover">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <span>{{ $staff->username }} - {{ $staff->name }}</span>

              @if ($staff->appraisal_category_id == NULL)
                <button type="button" data-bs-toggle="modal" data-bs-target="#form{{ $staff->id }}" data-id="{{ $staff->id }}" class="btn btn-sm py-0 btn-outline-secondary form-button">
                  -
                </button>
                @else
                <button type="button" data-bs-toggle="modal" data-bs-target="#form{{ $staff->id }}" data-id="{{ $staff->id }}" class="btn btn-sm py-0 btn-outline-success form-button">
                  {{ $staff->category }}
                </button>
                @endif

              <!-- POP UP -->
              <div class="modal fade" id="form{{ $staff->id }}" aria-labelledby="formlabel{{ $staff->id }}" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                  {!! Form::model($staff, [
                  'route' => ['appraisalapoint.update', $staff->id],
                  'method' => 'PATCH',
                  'id' => 'form_update',
                  'autocomplete' => 'off',
                  'files' => true,
                  'class' => 'form-appraisal-category',
                  'data-id' => $staff->id,
                  'data-toggle' => 'validator',
                  ]) !!}
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5" id="formlabel{{ $staff->id }}">Appraisal Form : {{ $staff->username }} - {{ $staff->name }}
                      </h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body align-items-start justify-content-center">
                      <div class="row mb-1">
                        <div class="mb-1">
                          {!! Form::select( 'appraisal_category_id'. $staff->id, $appraisal_category, @$value, ['class' => 'form-control select-input form-select', 'id' => 'appraisal_category_id'. $staff->id, 'placeholder' => 'Please Select'] ) !!}
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      {{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
                    </div>
                  </div>
                  {{ Form::close() }}
                </div>
              </div>
              <!-- POP UP -->

            </div>
          </div>

          @foreach($markers as $marker)
          <div class="row hover">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <span>&nbsp;&nbsp;<i class="bi-x-diamond-fill" style="font-size: 12px;"></i>&nbsp;&nbsp;{{ $marker->username }} - {{ $marker->name }}</span>
              <button type="button" class="pivot_delete" data-id="{{ $marker->id }}">
                <i class="bi-x-square-fill text-danger" aria-hidden="true"></i>
              </button>
            </div>
          </div>
          @endforeach

          <div class="mb-3"></div>
          @endforeach
        </div>
      </div>

    </div>

    <div class="col-6">
      {{ Form::open(['route' => ['appraisalapoint.store'], 'id' => 'form_store', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

      <div class="row mb-3">
        <div class="col-2">
          Evaluator
        </div>
        <div class="col-10">
          {!! Form::select( 'evaluator_id', $evaluator, @$value, ['class' => 'form-control select-input form-select', 'id' => 'evaluator_id', 'placeholder' => 'Please Select'] ) !!}
        </div>
      </div>

      <div class="row">
        <div class="col-2">
          Evaluatee
        </div>
        <div class="col-10">
          <div class="scrollable-div-2">
            @foreach($evaluatees as $evaluatee)
            <div class="form-check mb-1 g-3">
              <input class="form-check-input" name="evaluetee_id[]" type="checkbox" value="{{ $evaluatee->id }}" id="evaluatee_id{{ $evaluatee->id }}">
              <label class="form-check-label" for="evaluatee_id{{ $evaluatee->id }}">[{{ $evaluatee->department }}]<br />{{ $evaluatee->username }} - {{ $evaluatee->name }}</label>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-center m-3">
        {!! Form::submit('SUBMIT', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
      </div>

      {{ Form::close() }}
    </div>
  </div>

</div>
@endsection

@section('js')
////////////////////////////////////////////////////////////////////////////////////
$('.form-select').select2({
  placeholder: 'Please Select',
  width: '100%',
  allowClear: true,
  closeOnSelect: true,
});

$(document).on('click', '.form-button', function(e){
  var formid = $(this).data('id');

  $('#appraisal_category_id' + formid).select2({
    placeholder: 'Please Select',
    width: '100%',
    allowClear: true,
    closeOnSelect: true,
    dropdownParent: $('#form' + formid)
  });
});

////////////////////////////////////////////////////////////////////////////////////
// DELETE APOINT APPRAISAL
$(document).on('click', '.pivot_delete', function(e){
  var pivotId = $(this).data('id');
  SwalPivotDelete(pivotId);
  e.preventDefault();
});

function SwalPivotDelete(pivotId){
  swal.fire({
    title: 'DELETE',
    text: "Do you want to delete?",
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
          url: '{{ url('appraisalapoint') }}' + '/' + pivotId,
          data: {
            _token : $('meta[name=csrf-token]').attr('content'),
            id: pivotId,
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
          swal.fire('Error', 'Something wrong with ajax!', 'error');
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


////////////////////////////////////////////////////////////////////////////////////
// UPDATE APPRAISAL CATEGORY
$(".form-appraisal-category").on('submit', function (e) {
  var ids = $(this).data('id');

  e.preventDefault();
  $.ajax({
    url: '{{ url('appraisalapoint/update') }}',
    type: 'PATCH',
    data: {
      _token: '{!! csrf_token() !!}',
      id: ids,
      category_id: $('#appraisal_category_id' + ids).val(),
    },
    dataType: 'json',
    global: false,
    async: false,
    success: function (response) {
      $('#form').modal('hide');
      // var row = $('#form').parent().parent();
      // row.remove();
      swal.fire({
        title: 'Success!',
        text: response.message,
        icon: response.status
      }).then((result) => {
        if (result.isConfirmed) {
          location.reload();
        }
      });
    },
    error: function (resp) {
      const res = resp.responseJSON;
      $('#form').modal('hide');
      swal.fire('Error!', res.message, 'error');
    }
  });
});
@endsection