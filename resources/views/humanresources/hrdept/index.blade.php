@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;




?>
<ul class="nav nav-tabs">
	<li class="nav-item">
		<a class="nav-link active" aria-current="page" href="#">Active</a>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Dropdown</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item" href="#">Action</a></li>
			<li><a class="dropdown-item" href="#">Another action</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li>
		</ul>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="#">Link</a>
	</li>
	<li class="nav-item">
		<a class="nav-link disabled" aria-disabled="true">Disabled</a>
	</li>
</ul>





<div class="col-auto table-responsive">
	<p>Staffs</p>
	<table class="table table-hover table-sm col-auto">
		<thead>
			<tr>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach(Staff::where('active', 1)->get() as $s)
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
