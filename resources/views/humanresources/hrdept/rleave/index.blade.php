@extends('layouts.app')

@section('content')

<style>
  /* div {
  border: 1px solid black;
} */

  /* table,
  thead,
  tr,
  th {
    border: 1px solid black;
  } */
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Replacement Leave</h4>
  <div>
    <table id="replacement" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Date Start</th>
          <th>Date End</th>
          <th>Customer</th>
          <th>Reason</th>
          <th>Total</th>
          <th>Utilize</th>
          <th>Balance</th>
          <th>Remarks</th>
          <th>Edit</th>
          <th>Cancel</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($replacements as $replacement)

        <?php
        if ($replacement->belongstocustomer) {
          $customer = $replacement->belongstocustomer->customer;
        } else {
          $customer = "";
        }
        ?>
        
        <tr>
          <td>{{ $replacement->belongstostaff->hasmanylogin()->where('logins.active', 1)->first()->username }}</td>
          <td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $replacement->belongstostaff->name }}">{{ $replacement->belongstostaff->name }}</td>
          <td>{{ $replacement->date_start }}</td>
          <td>{{ $replacement->date_end }}</td>
          <td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $customer }}">{{ $customer }}</td>
          <td class="text-truncate" style="max-width: 150px;" data-toggle="tooltip" title="{{ $replacement->reason }}">{{ $replacement->reason }}</td>
          <td class="text-center">{{ $replacement->leave_total }}</td>
          <td class="text-center">{{ $replacement->leave_utilize }}</td>
          <td class="text-center">{{ $replacement->leave_balance }}</td>
          <td class="text-truncate" style="max-width: 100px;" data-toggle="tooltip" title="{{ $replacement->remarks }}">{{ $replacement->remarks }}</td>
          <td class="text-center">
            <a href="{{ route('rleave.edit', $replacement->id) }}">
              <i class="bi bi-pencil-square" style="font-size: 15px;"></i>
            </a>
          </td>
          <td class="text-center">
            <a href="#">
              <i class="bi bi-x-square" style="font-size: 15px;"></i>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="d-flex justify-content-center">
      {{ $replacements->links() }}
    </div>
  </div>
</div>
@endsection


@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#replacement').DataTable({
"paging": false,
"lengthMenu": [ [-1], ["All"] ],
"columnDefs": [
{ type: 'date', 'targets': [2] },
{ type: 'time', 'targets': [3] },
],
"order": [ 2, 'desc' ], // sorting the 6th column descending
responsive: true
})


$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
@endsection


@section('nonjquery')

@endsection