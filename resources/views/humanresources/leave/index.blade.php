@extends('layouts.app')

@section('content')
<?php
use Illuminate\Support\Str;
// 1st sekali check profile. checking utk email & emergency person. lock kat sini smpi user isi baru buleh apply cuti.

// check emergency person
$us = \Auth::user()->belongstostaff;
$emer = $us->hasmanyemergency()->get();
// check email
$email = $us->email;
$e =  $us->hasmanyemergency()->get();
$leaveAL =  $us->hasmanyleaveannual()->where('year', date('Y'))->first();
$leaveMC =  $us->hasmanyleavemc()->where('year', date('Y'))->first();
$leaveMa =  $us->hasmanyleavematernity()->where('year', date('Y'))->first();
// $leaveALMC =  $us->hasmanyleaveentitlements()->whereFirst('year', date('Y'));
// dd($leaveALMC);
?>
<div class="col-sm-12">
	<div class="col-auto table-responsive">
		<table class="table table-hover table-sm col-auto">
			<tr>
				<th>Attention</th>
				<td colspan="2">
					<p>
						Please complete your profile before applying your leave. Once completed, please proceed with leave application.<br />
						@if(is_null($email) && is_null($emer) && $emer->count() == 0)
							<a href="{{ route('profile.edit',  $us->id ) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-regular fa-user"></i>Profile</a>
						@else
							<a href="{{ route('leave.create' ) }}" class="btn btn-sm btn-outline-secondary">Leave Application</a>
						@endif
					</p>
				</td>
			</tr>
			<tr>
				<th rowspan="2">Annual Leave :</th>
				<td>Initialize :</td>
				<td>{{ $leaveAL->annual_leave + $leaveAL->annual_leave_adjustment }} days</td>
			</tr>
			<tr>
				<td>Balance:</td>
				<td><span class=" {{ ($leaveAL->annual_leave_balance < 4)?'text-danger font-weight-bold':'' }}">{{ $leaveAL->annual_leave_balance }} days</span></td>
			</tr>
			<tr>
				<th rowspan="2">Medical Certificate Leave :</th>
				<td>Initialize :</td>
				<td>{{ $leaveMC->mc_leave + $leaveMC->mc_adjustment }} days</td>
			</tr>
			<tr>
				<td>Balance :</td>
				<td><span class=" {{ ($leaveMC->mc_leave_balance < 4)?'text-danger font-weight-bold':'' }}">{{ $leaveMC->mc_leave_balance }} days</span></td>
			</tr>
			@if( $us->gender_id == 2 )
			<tr>
				<th rowspan="2">Maternity Leave :</th>
				<td>Initialize :</td>
				<td>{{ $leaveMa->maternity_leave + $leaveMa->maternity_leave_adjustment }} days</td>
			</tr>
			<tr>
				<td>Balance :</td>
				<td><span class=" {{ ($leaveMa->maternity_leave_balance < 4)?'text-danger font-weight-bold':'' }}">{{ $leaveMa->maternity_leave_balance }} days</span></td>
			</tr>
			@endif
			<tr>
				<th>Unpaid Leave :</th>
				<td colspan="2">{{  $us->hasmanyleave()->whereYear( 'date_time_start', date('Y') )->whereIn('leave_type_id', [3, 6])->get()->sum('period_day') }} days</td>
			</tr>
			@if($us->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->get()->sum('leave_balance') > 0)
			<tr>
				<th>
					Replacement Leave :
				</th>
				<td colspan="2">{{ $us->hasmanyleavereplacement()->sum('leave_balance') }} days</td>
			</tr>
			@endif
			@if($us->belongstoleaveapprovalflow->backup_approval == 1)
				<tr>
					<th>Backup Personnel :</th>
					<td colspan="2">
					<?php
					// find backup person according to its department
					// need to get the department 1st
					$dept = $us->belongstomanydepartment()->get();
					?>
						<ul>
						<!-- backup from own department -->
						@foreach($dept as $de)
							<li>
								{{ $de->name }}
								<?php
								$des = $de->belongstomanystaff()->where('active', 1)->where('staff_id', '<>', Auth::user()->staff_id)->get()->sortBy('name');
								?>
								<ol>
									@foreach($des as $dess)
										<li>{{ $dess->name }}</li>
									@endforeach
								</ol>
							</li>
						@endforeach
						<!-- backup from cross department -->
						<?php
						$crossbacku = $us->crossbackupto()?->wherePivot('active', 1)->get();
						?>
						@if($crossbacku)
							<li>
								<ol>
								@foreach($crossbacku as $key)
									<li>{{ $key->name }}</li>
								@endforeach
								</ol>
							</li>
						@endif
						</ul>
					</td>
				</tr>
			@endif
		</table>
	</div>
	
	<div class="col-auto table-responsive">
		<h4>Leave</h4>
	<!-- list of leaves -->
	<?php
	// dd(\Carbon\Carbon::now()->copy()->startOfYear());
	$starty = \Carbon\Carbon::now()->copy()->startOfYear();
	$lea =  $us->hasmanyleave()->where('date_time_start', '>=', $starty)->get();
	// dd($lea);
	?>
	@if( $lea->count() > 0 )
			<table class="table table-hover table-sm" id="leaves" style="font-size:12px">
				<thead>
					<tr>
						<th rowspan="2">ID</th>
						<th rowspan="2">Date Apply</th>
						<th rowspan="2">Leave</th>
						<th rowspan="2">Reason</th>
						<th colspan="2" >Date/Time Leave</th>
						<th rowspan="2">Period</th>
						<th rowspan="2">Code</th>
						<th rowspan="2">Approval, Remarks and Updated At</th>
						<th rowspan="2">Leave Status</th>
					</tr>
					<tr>
						<th>From</th>
						<th>To</th>
					</tr>
				</thead>
				<tbody>
	@foreach($lea as $leav)
	<?php
	$dts = \Carbon\Carbon::parse($leav->date_time_start)->format('Y');
	$dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y g:i a');
	$arr = str_split( $dts, 2 );
	// only available if only now is before date_time_start and active is 1
	$dtsl = \Carbon\Carbon::parse( $leav->date_time_start );
	$dt = \Carbon\Carbon::now()->lte( $dtsl );
	?>
					<tr>
						<td>
							<a href="#" class="btn btn-sm btn-outline-secondary" alt="Print PDF" title="Print PDF" target="_blank"><i class="far fa-file-pdf"></i></a>
							HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $arr[1] }}
						</td>
	<?php
	if ( ($leav->leave_type_id == 9) || ($leav->leave_type_id != 9 && $leav->half_type_id == 2) || ($leav->leave_type_id != 9 && $leav->half_type_id == 1) ) {
		$dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y g:i a');
		$dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y g:i a');

		if ($leav->leave_type_id != 9) {
			if ($leav->half_type_id == 2) {
				$dper = $leav->period_day.' Day';
			} elseif($leav->half_type_id == 1) {
				$dper = $leav->period_day.' Day';
			}
		}elseif ($leav->leave_type_id == 9) {
			$i = \Carbon\Carbon::parse($leav->period_time);
			$dper = $i->hour.' hour, '.$i->minute.' minutes';
		}

	} else {
		$dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y ');
		$dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y ');
		$dper = $leav->period_day.' day/s';
	}
	?>
						<td>{{ \Carbon\Carbon::parse($leav->created_at)->format('j M Y') }}</td>
						<td>{{ $leav->belongstooptleavetype->leave_type_code }}</td>
						<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">{{ Str::of($leav->reason)->words(3, ' >') }}</td>
						<td>{{ $dts }}</td>
						<td>{{ $dte }}</td>
						<td>{{ $dper }}</td>
						<td>{{ $leav->verify_code }}</td>
						<td>
							<table class="table table-hover table-sm">
								<tbody>
									@if($leav->hasoneleaveapprovalbackup()->get()->isNotEmpty())
										<tr>
											<!-- <td>Backup {{ $leav->hasoneleaveapprovalbackup()->first()->belongstostaff?->name }}</td> -->
											<td>Backup</td>
											<td>{{ $leav->hasoneleaveapprovalbackup()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasoneleaveapprovalsupervisor()->get()->isNotEmpty())
										<tr>
											<!-- <td>Supervisor {{ $leav->hasoneleaveapprovalsupervisor()->first()->belongstostaff?->name }}</td> -->
											<td>Supervisor</td>
											<td>{{ $leav->hasoneleaveapprovalsupervisor()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasoneleaveapprovalhod()->get()->isNotEmpty())
										<tr>
											<!-- <td>HOD {{ $leav->hasoneleaveapprovalhod()->first()->belongstostaff?->name }}</td> -->
											<td>HOD</td>
											<td>{{ $leav->hasoneleaveapprovalhod()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasoneleaveapprovaldir()->get()->isNotEmpty())
										<tr>
											<!-- <td>Director {{ $leav->hasoneleaveapprovaldir()->first()->belongstostaff?->name }}</td> -->
											<td>Director</td>
											<td>{{ $leav->hasoneleaveapprovaldir()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasoneleaveapprovalhr()->get()->isNotEmpty())
										<tr>
											<!-- <td>HR {{ $leav->hasoneleaveapprovalhr()->first()->belongstostaff?->name }}</td> -->
											<td>HR</td>
											<td>{{ $leav->hasoneleaveapprovalhr()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif
								</tbody>
							</table>
						</td>
						<td>
							@if(is_null($leav->leave_status_id))
								Pending
								@if($dt === true )
									<a href="{{ __('route') }}" class="btn btn-sm btn-outline-secondary cancel_btn" id="cancel_btn_{{ $leav->id }}" data-id="{{ $leav->id }}" alt="Cancel" title="Cancel"><i class="fas fa-ban"></i></a>
								@endif
							@else
								{{ $leav->belongstooptleavestatus->status }}
							@endif
						</td>
					</tr>
	@endforeach
				</tbody>
			</table>
	</div>
	@else
			<p class="card-text text-justify text-lead">No record for your leave. Click on "Leave Application" to apply a leave.</p>
	@endif
	<p>&nbsp;</p>
	<div class="col-auto table-responsive">
		<h4>Approver</h4>
		<table class="table table-hover table-sm" id="approver" style="font-size:12px">
			<thead>
				<tr>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// tooltip on reason
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();   
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#leaves').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"order": [[0, "desc" ]],	// sorting the 6th column descending
	responsive: true
});

/////////////////////////////////////////////////////////////////////////////////////////
// cancel leave
$(document).on('click', '.cancel_btn', function(e){
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID){
	swal.fire({
		title: 'Cancel Leave',
		text: 'Are you sure to cancel this leave?',
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
					url: '{{ url('leavecancel') }}' + '/' + ackID,
					type: 'PATCH',
					dataType: 'json',
					data: {
							id: ackID,
							cancel: 3,
							_token : $('meta[name=csrf-token]').attr('content')
					},
				})
				.done(function(response){
					swal.fire('Accept', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
					// $('#cancel_btn_' + ackID).parent().parent().remove();
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
			swal.fire('Cancel Action', 'Leave is still active.', 'info')
		}
	});
}
//auto refresh right after clicking OK button
$(document).on('click', '.swal2-confirm', function(e){
	window.location.reload(true);
});


/////////////////////////////////////////////////////////////////////////////////////////
@endsection

