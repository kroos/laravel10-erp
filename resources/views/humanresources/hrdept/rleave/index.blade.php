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

        @if ($replacement->belongstocustomer)
        {{ $customer = $replacement->belongstocustomer->customer }}
        @else
        {{ $customer = "" }}
        @endif
        
        <tr>
          <th>{{ $replacement->belongstostaff->hasmanylogin()->where('logins.active', 1)->first()->username }}</th>
          <th class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $replacement->belongstostaff->name }}">{{ $replacement->belongstostaff->name }}</th>
          <th>{{ $replacement->date_start }}</th>
          <th>{{ $replacement->date_end }}</th>
          <th class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $customer }}">{{ $customer }}</th>
          <th class="text-truncate" style="max-width: 150px;" data-toggle="tooltip" title="{{ $replacement->reason }}">{{ $replacement->reason }}</th>
          <th class="text-center">{{ $replacement->leave_total }}</th>
          <th class="text-center">{{ $replacement->leave_utilize }}</th>
          <th class="text-center">{{ $replacement->leave_balance }}</th>
          <th class="text-truncate" style="max-width: 100px;" data-toggle="tooltip" title="{{ $replacement->remarks }}">{{ $replacement->remarks }}</th>
          <th class="text-center">
            <a href="#">
              <i class="bi bi-pencil-square" style="font-size: 15px;"></i>
            </a>
          </th>
          <th class="text-center">
            <a href="#">
              <i class="bi bi-x-square" style="font-size: 15px;"></i>
            </a>
          </th>
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
"order": [ 0, 'asc' ], // sorting the 6th column descending
responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
$(document).ready(function(){
$('[data-bs-toggle="tooltip"]').tooltip();
});}
);
@endsection

@section('nonjquery')

@endsection