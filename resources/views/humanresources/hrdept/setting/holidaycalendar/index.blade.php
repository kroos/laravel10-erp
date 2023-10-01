@extends('layouts.app')

@section('content')
<?php
use \App\Models\HumanResources\HRHolidayCalendar;

use \Carbon\Carbon;
?>

<div class="col-sm-12 row">
	@include('humanresources.hrdept.navhr')
	<h4>Holiday Calendar &nbsp; <a href="{{ route('holidaycalendar.create') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-calendar-plus fa-beat"></i> &nbsp;Add Holiday</a> </h4>

	<table class="table table-hover table-sm" style="font-size:12px">
	@foreach(HRHolidayCalendar::groupByRaw('YEAR(date_start)')->selectRaw('YEAR(date_start) as year')->orderBy('date_start', 'DESC')->get() as $tp)
		<thead>
			<tr>
				<th class="text-center" colspan="6">&nbsp;</th>
			</tr>
			<tr>
				<th class="text-center" colspan="6">Holiday Calendar ({{ $tp->year }})</th>
			</tr>
			<tr>
				<th>From</th>
				<th>To</th>
				<th>Holiday</th>
				<th>Duration</th>
				<th>Remarks</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		@foreach(HRHolidayCalendar::whereYear('date_start', $tp->year)->orderBy('date_start', 'ASC')->get() as $t)
			<tr>
				<td>{{ Carbon::parse($t->date_start)->format('D, j M Y') }}</td>
				<td>{{ Carbon::parse($t->date_end)->format('D, j M Y') }}</td>
				<td>{{ $t->holiday }}</td>
				<td>{{ Carbon::parse($t->date_start)->daysUntil($t->date_end, 1)->count() }} day/s</td>
				<td>{{ $t->remarks }}</td>
				<td>
					<a class="btn btn-sm btn-outline-secondary" href="{{ route('holidaycalendar.edit', $t->id) }}"><i class="far fa-edit"></i></a>
					<span class="btn btn-sm btn-outline-secondary text-danger delete_button" href="{{ route('holidaycalendar.destroy', $t->id) }}" id="delete_product_{{ $t->id }}" data-id="{{ $t->id }}"><i class="far fa-trash-alt"></i></span>
				</td>
			</tr>
		@endforeach
		</tbody>
	@endforeach
	</table>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// ajax post delete row
$(document).on('click', '.delete_button', function(e){

	var productId = $(this).data('id');
	SwalDelete(productId);
	e.preventDefault();
});

function SwalDelete(productId){
	swal.fire({
		title: 'Are you sure?',
		text: "It will be deleted permanently!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					url: '{{ url('holidaycalendar') }}' + '/' + productId,
					type: 'DELETE',
					data: {
							_token : $('meta[name=csrf-token]').attr('content'),
							id: productId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Deleted!', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
					//$('#delete_product_' + productId).parent().parent().remove();
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
			swal.fire('Cancelled', 'Your data is safe from delete', 'info')
		}
	});
}

@endsection
