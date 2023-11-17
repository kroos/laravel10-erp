@extends('layouts.app')

@section('content')
<?php
use Illuminate\Support\Facades\DB;
use App\Models\Staff;
use \Carbon\Carbon;
?>
<div class="container row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h2>Staffs Overtime&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('overtime.create') }}"><i class="fa-solid fa-person-circle-plus fa-beat"></i> Add Staff Overtime</a></h2>
	<div class="d-flex justify-content-center">
	</div>
	<div class="table-responsive">
		<table id="overtime" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th rowspan="2">ID</th>
					<th rowspan="2">Name</th>
					<th rowspan="2">Date</th>
					<th colspan="2" rowspan="1">Overtime</th>
					<th rowspan="2">Duration</th>
					<th rowspan="2">Assign By</th>
					<th rowspan="2">Remarks</th>
					<th rowspan="2">#</th>
				</tr>
				<tr>
					<th>Start Time</th>
					<th>End Time</th>
				</tr>
			</thead>
			<tbody>

<?php
// who am i?
$me1 = \Auth::user()->belongstostaff->div_id == 1;		// hod
$me2 = \Auth::user()->belongstostaff->div_id == 5;		// hod assistant
$me3 = \Auth::user()->belongstostaff->div_id == 4;		// supervisor
$me4 = \Auth::user()->belongstostaff->div_id == 3;		// HR
$me5 = \Auth::user()->belongstostaff->authorise_id == 1;	// admin
$me6 = \Auth::user()->belongstostaff->div_id == 2;		// director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;
?>
				@foreach($overtime as $key)
<?php
if ($me1) {																				// hod
	if ($deptid == 21) {																// hod | dept prod A
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
	} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 16 || Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 17;
	} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
	}
} elseif($me2) {																		// not hod | asst hod
	if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
		$ha = true;
	} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == 7;
	}
} elseif($me3) {																		// not hod | not asst hod | supervisor
	if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || (Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
		$ha = Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || (Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && Staff::find($key->staff_id)->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch);
	}
} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
	$ha = true;
} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
	$ha = true;
} else {
	$ha = false;
}
?>
					@if( $ha )
						<tr>
							<td>{{ $key->belongstostaff->hasmanylogin()->where('active', 1)->first()?->username }}</td>
							<td>{{ $key->belongstostaff?->name }}</td>
							<td>{{ Carbon::parse($key->ot_date)->format('j M Y') }}</td>
							<td>{{ Carbon::parse($key->belongstoovertimerange?->start)->format('g:i a') }}</td>
							<td>{{ Carbon::parse($key->belongstoovertimerange?->end)->format('g:i a') }}</td>
							<td>{{ $key->belongstoovertimerange?->total_time }}</td>
							<td>{{ $key->belongstoassignstaff?->name }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($key->remark)??' ' }}">{{ Str::limit($key->remark, 8, ' >') }}</td>
							<td>
								<a href="{{ route('overtime.edit', $key->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="bi bi-pencil-square" style="font-size: 15px;"></i>
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary delete_overtime" data-id="{{ $key->id }}" >
									<i class="fa-regular fa-trash-can"></i>
								</button>
							</td>
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="d-flex justify-content-center">
	</div>
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
$('#overtime').DataTable({
	// "lengthMenu": [ [10,25,50,100,150,200,-1], [10,25,50,100,150,200,"All"] ],
	"lengthMenu": [ [-1], ["All"] ],
	"columnDefs": [
					{ type: 'date', 'targets': [2] },
					{ type: 'time', 'targets': [3] },
					{ type: 'time', 'targets': [4] },
				],
	"order": [[2, "DESC" ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

/////////////////////////////////////////////////////////////////////////////////////////
// DELETE
$(document).on('click', '.delete_overtime', function(e){
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable){
	swal.fire({
		title: 'Delete Overtime',
		text: 'Are you sure to delete this overtime?',
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
					url: '{{ url('overtime') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
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
	})
};

@endsection

@section('nonjquery')
@endsection
