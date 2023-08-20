@extends('layouts.app')

@section('content')
<?php
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
?>
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<h2>Staffs</h2>
	<div class="table-responsive">
		<table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					@if(auth()->user()->belongstostaff->authorise_id == 1)
					<th>Staff ID</th>
					@endif
					<th>ID</th>
					<th>Name</th>
					<th>Group</th>
					<!-- <th>Gender</th> -->
					<th>Nationality</th>
					<th>Marital Status</th>
					<th>Category</th>
					<th>Department</th>
					<th>Location</th>
					<th>Leave Flow</th>
					<th>Phone</th>
				<!-- <th>CIMB Acc</th>
					<th>EPF</th>
					<th>Income Tax</th>
					<th>SOCSO</th>
					<th>Join</th>
					<th>Confirmed</th> -->
				</tr>
			</thead>
			<tbody class="table-group-divider">
<?php
// who am i?
$me1 = \Auth::user()->belongstostaff->where('div_id', 1)->get();		// hod
$me2 = \Auth::user()->belongstostaff->where('div_id', 5)->get();		// hod assistant
$me3 = \Auth::user()->belongstostaff->where('div_id', 4)->get();		// supervisor
$me4 = \Auth::user()->belongstostaff->where('div_id', 3)->get();		// HR
$me5 = \Auth::user()->belongstostaff->where('authorise_id', 1)->get();	// admin
$me6 = \Auth::user()->belongstostaff->where('div_id', 2)->get();		// director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;
?>
				@foreach(Staff::where('active', 1)->get() as $s)
<?php
// $ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == 1 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
// dump($ha);
// if(($me1 && $deptid != 21) && ($me1 && $deptid != 28)) {								// other HOD not in production
// if(($me1 || $me1) && ($deptid != 21 && $deptid != 28)) {								// other HOD not in production
// 	$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
// } elseif(($me1 || $me2) && ($deptid == 21 || $deptid == 28)) {							// other HOD in production
// 	$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
// } elseif($me3) {
// 	$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == $category || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch;
//  $ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch;
// }

if ($category == 1) {							// office
	if ($branch == 1) {							// office | branch A
		if ($deptid == 21) {					// office | branch A | dept prod A
			if ($me1) {							// office | branch A | dept prod A | hod
				$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
			}
		} else {								// office | branch A | no dept prod A
			if ($me1) {							// office | branch A | no dept prod A | no dept HR | hod
				$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
			}
		}
	} else {									// office | no branch A
		if ($branch == 2) {						// office | no branch A | branch B
			if ($deptid == 28) {				// office | no branch A | branch B | dept prod B
				if ($me1) {						// office | no branch A | branch B | dept prod B | hod
					$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid || $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2;
				}
			} else {							// office | no branch A | branch B | no dept prod B
				if ($deptid == 14) {			// office | no branch A | branch B | no dept prod B | dept HR
					if ($me1) {					// office | no branch A | branch B | no dept prod B | dept HR | hod
						$ha = true;
					} else {					// office | no branch A | branch B | no dept prod B | dept HR | no hod
						if ($me2) {				// office | no branch A | branch B | no dept prod B | dept HR | no hod | asst hod
							$ha = true;
						}
					}
				} else {						// office | no branch A | branch B | no dept prod B | no dept HR
					if ($me1) {					// office | no branch A | branch B | no dept prod B | no dept HR | hod
						$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
						dump($ha.'hod');
					} else {					// office | no branch A | branch B | no dept prod B | no dept HR | no hod
						if ($me2) {				// office | no branch A | branch B | no dept prod B | no dept HR | no hod | asst hod
							$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->id == $deptid;
							dump($ha.'asst hod');
						} else {				// office | no branch A | branch B | no dept prod B | no dept HR | no hod | no asst hod
							if ($me6) {	// office | no branch A | branch B |  no dept prod B | no dept HR | no hod | no asst hod | dir & hr
								$ha = true;
								dump($ha);
							} else {			// office | no branch A | branch B |  no dept prod B | no dept HR | no hod | no asst hod | no dir & hr
								if ($me5) {		// office | no branch A | branch B |  no dept prod B | no dept HR | no hod | no asst hod | no dir & hr | admin
									$ha = true;
								}
							}
						}
					}
				}
			}
		}
	}
} else {										// production
	if ($branch == 1) {							// production | branch A
		if ($me3) {								// production | branch A | supervisor
			$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch;
		}
	} else {									// production | not branch A
		if ($branch == 2) {						// production | not branch A | branch B
			if ($me3) {							// production | not branch A | branch B | supervisor
				$ha = $s->belongstomanydepartment()->wherePivot('main', 1)->first()->category_id == 2 && $s->belongstomanydepartment()->wherePivot('main', 1)->first()->branch_id == $branch;
			}
		}
	}
}

?>
					@if( $ha )
						<tr>
							@if(auth()->user()->belongstostaff->authorise_id == 1)
							<td>{{ $s->id }}</td>
							@endif
							<td><a href="{{ route('staff.show', $s->id) }}" alt="Detail" title="Detail">{{ $s->hasmanylogin()->where('active', 1)->first()?->username }}</a></td>
							<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="
								<div class='d-flex flex-column align-items-center text-center p-3 py-5'>
									<img class='rounded-5 mt-3' width='180px' src='{{ asset('storage/user_profile/' . $s->image) }}'>
									<span class='font-weight-bold'>{{ $s->name }}</span>
									<span class='font-weight-bold'>{{ $s->hasmanylogin()->where('active', 1)->first()?->username }}</span>
									<span> </span>
								</div>
							">{{ $s->name }}</td>
							<td>{{ $s->belongstorestdaygroup?->group }}</td>
							<!-- <td>{{ $s->belongstogender?->gender }}</td> -->
							<td>{{ $s->belongstonationality?->country }}</td>
							<td>{{ $s->belongstomaritalstatus?->marital_status }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->belongstocategory->category }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->department }}</td>
							<td>{{ $s->belongstomanydepartment()->wherePivot('main', 1)->first()?->belongstobranch->location }}</td>
							<td>{{ $s->belongstoleaveapprovalflow?->description }}</td>
							<td>{{ $s->mobile }}</td>
		<!-- 				<td>{{ $s->cimb_account }}</td>
							<td>{{ $s->epf_account }}</td>
							<td>{{ $s->income_tax_no }}</td>
							<td>{{ $s->socso_no }}</td>
							<td>{{ \Carbon\Carbon::parse($s->join)->format('j M Y ') }}</td>
							<td>{{ \Carbon\Carbon::parse($s->confirmed)->format('j M Y ') }}</td> -->
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>
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
$('#staff').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"order": [[1, "asc" ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection
