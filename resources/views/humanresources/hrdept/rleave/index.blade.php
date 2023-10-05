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

  .btn-sm-custom {
		padding: 0px;
		background: none;
    border: none;
    font-size: 15px;
    width: 100%;
    height: 100%;
	}
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Replacement Leave&nbsp; <a class="btn btn-sm btn-outline-secondary" href="{{ route('rleave.create') }}"><i class="fa-solid fa-person-walking-arrow-loop-left fa-beat"></i> Add Replacement Leave</a></h4>
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
            <a href="">
              <i class="bi bi-x-square delete_replacement" data-id="{{ $replacement->id }}" data-table="replacement" style="font-size: 15px;"></i>
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


/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_replacement', function(e){
	var ackID = $(this).data('id');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackTable){
	swal.fire({
		title: 'Delete Replacement Leave',
		text: 'Are you sure to delete this replacement?',
		icon: 'info',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancel',
		confirmButtonText: 'Yes',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('rleave') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						table: ackTable,
						_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with ajax!', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancel Action', '', 'info')
		}
	});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function(e){
	window.location.reload(true);
});
@endsection


@section('nonjquery')

@endsection
