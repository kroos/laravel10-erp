@extends('layouts.app')

@section('content')
<?php
$user = \Auth::user()->belongstostaff->id;

$appraisals = DB::table('pivot_apoint_appraisals')
  ->join('logins', 'logins.staff_id', '=', 'pivot_apoint_appraisals.evaluatee_id')
  ->join('staffs', 'staffs.id', '=', 'pivot_apoint_appraisals.evaluatee_id')
  ->where('pivot_apoint_appraisals.evaluator_id', $user)
  ->where('logins.active', 1)
  ->where('pivot_apoint_appraisals.active', 1)
  ->whereNull('pivot_apoint_appraisals.deleted_at')
  ->select('pivot_apoint_appraisals.id as apointid', 'staffs.name', 'logins.username', 'staffs.appraisal_category_id')
  ->orderBy('logins.username', 'ASC')
  ->get();
?>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <div class="row mt-3">
    <div class="col-md-2">
      <h4>Appraisal </h4>
    </div>
  </div>

  <div class="row">&nbsp;</div>

  <div>
    <table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th class="text-center" style="max-width: 60px;">ID</th>
          <th>Name</th>
          <th style="max-width: 150px;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($appraisals as $appraisal)

        <tr>
          <td class="text-center">
            {{ $appraisal->username }}
          </td>
          <td data-toggle="tooltip" title="{{ $appraisal->name }}">
            <input type="text" readonly value="{{ $appraisal->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <!-- IF ERROR : Please Apoint A Form To Every Evaluatees -->
          <td class="text-center">
            <a href="{{ route('appraisalmark.create', ['id' => $appraisal->apointid]) }}" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-pencil-square" style="font-size: 15px;"> PENDING</i>
            </a>
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
$('#staff').DataTable({
"paging": false,
"order": [ 0, 'asc' ],
"columnDefs": [
                { type: 'string', 'targets': [0] },
                { type: 'string', 'targets': [1] },
              ],
responsive: true
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
});
@endsection