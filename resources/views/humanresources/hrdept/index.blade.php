@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;





?>
<div class="col-auto table-responsive">
	<p>Staffs</p>
	<table class="table table-hover table-sm col-auto">
		<thead>
			<tr>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach(Staff::all() as $s)
			<tr>
				<td>ID</td>
				<td>Name</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();   
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#leaves').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"order": [[0, "asc" ]],	// sorting the 6th column descending
	responsive: true
});

@endsection