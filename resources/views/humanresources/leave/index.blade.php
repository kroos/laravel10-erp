@extends('layouts.app')

@section('content')
<?php
use Illuminate\Support\Str;
use \App\Models\HumanResources\HRLeaveApprovalSupervisor;
use \App\Models\HumanResources\HRLeaveApprovalHOD;
use \App\Models\HumanResources\HRLeaveApprovalDirector;
use \App\Models\HumanResources\HRLeaveApprovalHR;
use App\Models\HumanResources\OptLeaveStatus;
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

// for supervisor and hod approval
// $ls['results'] = [];
if(\Auth::user()->belongstostaff->div_id != 2) {									// not director approval: supervisor, hod, hr
	$c = OptLeaveStatus::whereIn('id', [4,5])->get();								// only rejected and approve
} elseif(\Auth::user()->belongstostaff->div_id == 2) {								// only director
	$c = OptLeaveStatus::whereIn('id', [4,5,6])->get();								// only rejected, approve and waived
}
foreach ($c as $v) {
	$ls[] = ['id' => $v->id, 'text' => $v->status];
}
// print_r ($ls);
// exit;


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
				<td>{{ $leaveMC->mc_leave + $leaveMC->mc_leave_adjustment }} days</td>
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
			@if($us->belongstoleaveapprovalflow?->backup_approval == 1)
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
						@if($crossbacku->isNotEmpty())
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
							<a href="{{ route('leave.show', $leav->id) }}" class="btn btn-sm btn-outline-secondary" alt="Print PDF" title="Print PDF" target="_blank"><i class="far fa-file-pdf"></i></a>
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
						<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $leav->reason }}">{{ Str::of($leav->reason)->words(3, ' >') }}</td>
						<td>{{ $dts }}</td>
						<td>{{ $dte }}</td>
						<td>{{ $dper }}</td>
						<td>{{ $leav->verify_code }}</td>
						<td>
							<table class="table table-hover table-sm">
								<tbody>
									@if($leav->hasmanyleaveapprovalbackup()->get()->isNotEmpty())
										<tr>
											<!-- <td>Backup {{ $leav->hasmanyleaveapprovalbackup()->first()->belongstostaff?->name }}</td> -->
											<td>Backup</td>
											<td>{{ $leav->hasmanyleaveapprovalbackup()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasmanyleaveapprovalsupervisor()->get()->isNotEmpty())
										<tr>
											<!-- <td>Supervisor {{ $leav->hasmanyleaveapprovalsupervisor()->first()->belongstostaff?->name }}</td> -->
											<td>Supervisor</td>
											<td>{{ $leav->hasmanyleaveapprovalsupervisor()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasmanyleaveapprovalhod()->get()->isNotEmpty())
										<tr>
											<!-- <td>HOD {{ $leav->hasmanyleaveapprovalhod()->first()->belongstostaff?->name }}</td> -->
											<td>HOD</td>
											<td>{{ $leav->hasmanyleaveapprovalhod()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasmanyleaveapprovaldir()->get()->isNotEmpty())
										<tr>
											<!-- <td>Director {{ $leav->hasmanyleaveapprovaldir()->first()->belongstostaff?->name }}</td> -->
											<td>Director</td>
											<td>{{ $leav->hasmanyleaveapprovaldir()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
										</tr>
									@endif

									@if($leav->hasmanyleaveapprovalhr()->get()->isNotEmpty())
										<tr>
											<!-- <td>HR {{ $leav->hasmanyleaveapprovalhr()->first()->belongstostaff?->name }}</td> -->
											<td>HR</td>
											<td>{{ $leav->hasmanyleaveapprovalhr()->first()->belongstoleavestatus?->status ?? 'Pending' }}</td>
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

	<?php
	$x = \Auth::user()->belongstostaff->hasmanyleaveapprovalbackup()->whereNull('leave_status_id')->get();	// user is a backup for some1 else
	// $s1 = \Auth::user()->belongstostaff()->whereIn('div_id', [4, 3])->get()->count();					// user is a supervisor and HR
	$s1 = \Auth::user()->belongstostaff->div_id == 4 || (\Auth::user()->belongstostaff->div_id == 1 && \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14);	// supervisor and hod HR
	// $h1 = \Auth::user()->belongstostaff()->whereIn('div_id', [1, 3])->get()->count();					// user is a HOD and HR
	$h1 = \Auth::user()->belongstostaff->div_id == 1 || (\Auth::user()->belongstostaff->div_id == 1 && \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14);	// HOD and hod HR
	// dd($h1);
	// $d1 = \Auth::user()->belongstostaff()->whereIn('div_id', [2, 3])->get()->count();					// user is a director and HR
	$d1 = \Auth::user()->belongstostaff->div_id == 2 || (\Auth::user()->belongstostaff->div_id == 1 && \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14);	// dir and hod HR
	// $r1 = \Auth::user()->belongstostaff()->where('div_id', 3)->get()->count();								// user is a HR
	$r1 = \Auth::user()->belongstostaff->div_id == 1 && \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14;													// hod HR
	?>

	<p>&nbsp;</p>
	@if($x->isNotEmpty())
	<div class="col-auto table-responsive">
		<h4>Backup Approval</h4>
		<table class="table table-hover table-sm" id="bapprover" style="font-size:12px">
			<thead>
				<tr>
					<th rowspan="2">Name</th>
					<th rowspan="2">Leave</th>
					<th rowspan="2">Reason</th>
					<th colspan="2">Date/Time Leave</th>
					<th rowspan="2">Period</th>
					<th rowspan="2">Leave Status</th>
				</tr>
				<tr>
					<th>From</th>
					<th>To</th>
				</tr>
			</thead>
			<tbody>
				@foreach($x as $a)
				<?php
				// dd($a);
				if ( ($a->belongstostaffleave->leave_type_id == 9) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 2) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 1) ) {
					$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y g:i a');
					$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y g:i a');

					if ($a->belongstostaffleave->leave_type_id != 9) {
						if ($a->belongstostaffleave->half_type_id == 2) {
							$dper = $a->belongstostaffleave->period_day.' Day';
						} elseif($a->belongstostaffleave->half_type_id == 1) {
							$dper = $a->belongstostaffleave->period_day.' Day';
						}
					}elseif ($a->belongstostaffleave->leave_type_id == 9) {
						$i = \Carbon\Carbon::parse($a->belongstostaffleave->period_time);
						$dper = $i->hour.' hour, '.$i->minute.' minutes';
					}

				} else {
					$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y ');
					$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y ');
					$dper = $a->belongstostaffleave->period_day.' day/s';
				}
				$z = \Carbon\Carbon::parse(now())->daysUntil($a->belongstostaffleave->date_time_start, 1)->count();
				if(3 <= $z && $z >= 2){
					$u = 'table-warning';
				} elseif($z < 2){
					$u = 'table-danger';
				} elseif($z > 3){
					$u = NULL;
				}
				?>
				<tr class="{{ $u }}" >
					<td>{{ $a->belongstostaffleave->belongstostaff->name }}</td>
					<td>{{ $a->belongstostaffleave->belongstooptleavetype->leave_type_code }}</td>
					<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $a->belongstostaffleave->reason }}">{{ str($a->belongstostaffleave->reason)->words(3, ' >') }}</td>
					<td>{{ $dts }}</td>
					<td>{{ $dte }}</td>
					<td>{{ $dper }}</td>
					<td>
						<a href="{{ __('route') }}" class="btn btn-sm btn-outline-secondary rapprover_btn" id="rapprover_btn_{{ $a->id }}" data-id="{{ $a->id }}" alt="Replacement Approver" title="Replacement Approver"><i class="bi bi-box-arrow-in-down"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif

	<p>&nbsp;</p>

	@if($s1)
		@if(HRLeaveApprovalSupervisor::whereNull('leave_status_id')->get()->count())
			<div class="col-auto table-responsive">
				<h4>Supervisor Approval</h4>
				<table class="table table-hover table-sm" id="sapprover" style="font-size:12px">
					<thead>
						<tr>
							<th rowspan="2">Name</th>
							<th rowspan="2">Leave</th>
							<th rowspan="2">Reason</th>
							<th colspan="2">Date/Time Leave</th>
							<th rowspan="2">Period</th>
							<th rowspan="2">Leave Status</th>
						</tr>
						<tr>
							<th>From</th>
							<th>To</th>
						</tr>
					</thead>
					<tbody>
						@foreach(HRLeaveApprovalSupervisor::whereNull('leave_status_id')->get() as $a)
							<?php
							$ul = $a->belongstostaffleave->belongstostaff->belongstomanydepartment->first()->branch_id;				//get user leave branch_id
							$us = \Auth::user()->belongstostaff->belongstomanydepartment->first()->branch_id;						//get user supervisor branch_id
							// echo $ul.' | '.$us;

								if ( ($a->belongstostaffleave->leave_type_id == 9) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 2) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 1) ) {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y g:i a');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y g:i a');

									if ($a->belongstostaffleave->leave_type_id != 9) {
										if ($a->belongstostaffleave->half_type_id == 2) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										} elseif($a->belongstostaffleave->half_type_id == 1) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										}
									}elseif ($a->belongstostaffleave->leave_type_id == 9) {
										$i = \Carbon\Carbon::parse($a->belongstostaffleave->period_time);
										$dper = $i->hour.' hour, '.$i->minute.' minutes';
									}

								} else {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y ');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y ');
									$dper = $a->belongstostaffleave->period_day.' day/s';
								}
								$z = \Carbon\Carbon::parse(now())->daysUntil($a->belongstostaffleave->date_time_start, 1)->count();
								if(3 >= $z && $z >= 2){
									$u = 'table-warning';
								} elseif($z < 2){
									$u = 'table-danger';
								} else {
									$u = NULL;
								}
							?>
							@if($ul == $us)
								<tr class="{{ $u }}" >
									<td>{{ $a->belongstostaffleave->belongstostaff?->name }}</td>
									<td>{{ $a->belongstostaffleave->belongstooptleavetype?->leave_type_code }}</td>
									<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $a->belongstostaffleave->reason }}">{{ str($a->belongstostaffleave->reason)->words(3, ' >') }}</td>
									<td>{{ $dts }}</td>
									<td>{{ $dte }}</td>
									<td>{{ $dper }}</td>
									<td>
										<!-- Button trigger modal -->
										<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>

										<!-- Modal for supervisor approval-->
										<div class="modal fade" id="sapproval{{ $a->id }}" aria-labelledby="suplabel{{ $a->id }}" aria-hidden="true">
										<!-- <div class="modal fade" id="sapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
											<div class="modal-dialog modal-dialog-centered">
												<div class="modal-content">
													<div class="modal-header">
														<h1 class="modal-title fs-5" id="suplabel{{ $a->id }}">Supervisor Approval</h1>
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
													</div>
													<div class="modal-body">
														{{ Form::open(['route' => ['leavestatus.supervisorstatus'], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
														{{ Form::hidden('id', $a->id) }}

														@foreach($ls as $k => $val)
														<div class="form-check form-check-inline">
															<input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="supstatus{{ $a->id.$val['id'] }}" class="form-check-input">
															<label class="form-check-label" for="supstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
														</div>
														@endforeach

														<div class="mb-3 row">
															<div class="form-group row {{ $errors->has('verify_code') ? 'has-error' : '' }}">
																<label for="supcode{{ $val['id'] }}" class="col-auto col-form-label col-form-label-sm">Verify Code :</label>
																<div class="col-auto">
																	<input type="text" name="verify_code" value="{{ @$value }}" id="supcode{{ $val['id'] }}" class="form-control form-control-sm" placeholder="Verify Code">
																</div>
															</div>
														</div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
														{{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
													</div>
														{{ Form::close() }}
												</div>
											</div>
										</div>

									</td>
								</tr>
							@endif
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	@endif

	<p>&nbsp;</p>
	@if($h1)
		@if(HRLeaveApprovalHOD::whereNull('leave_status_id')->get()->count())
			<div class="col-auto table-responsive">
				<h4>Head Of Department Approval</h4>
				<table class="table table-hover table-sm" id="sapprover" style="font-size:12px">
					<thead>
						<tr>
							<th rowspan="2">Name</th>
							<th rowspan="2">Leave</th>
							<th rowspan="2">Reason</th>
							<th colspan="2">Date/Time Leave</th>
							<th rowspan="2">Period</th>
							<th rowspan="2">Leave Status</th>
						</tr>
						<tr>
							<th>From</th>
							<th>To</th>
						</tr>
					</thead>
					<tbody>
						@foreach(HRLeaveApprovalHOD::whereNull('leave_status_id')->get() as $a)
							<?php
							$ul = $a->belongstostaffleave->belongstostaff->belongstomanydepartment->first()->branch_id;				//get user leave branch_id
							$us = \Auth::user()->belongstostaff->belongstomanydepartment->first()->branch_id;						//get user hod branch_id
							// echo $ul.' | '.$us;

								if ( ($a->belongstostaffleave->leave_type_id == 9) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 2) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 1) ) {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y g:i a');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y g:i a');

									if ($a->belongstostaffleave->leave_type_id != 9) {
										if ($a->belongstostaffleave->half_type_id == 2) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										} elseif($a->belongstostaffleave->half_type_id == 1) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										}
									}elseif ($a->belongstostaffleave->leave_type_id == 9) {
										$i = \Carbon\Carbon::parse($a->belongstostaffleave->period_time);
										$dper = $i->hour.' hour, '.$i->minute.' minutes';
									}

								} else {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y ');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y ');
									$dper = $a->belongstostaffleave->period_day.' day/s';
								}
								$z = \Carbon\Carbon::parse(now())->daysUntil($a->belongstostaffleave->date_time_start, 1)->count();
								if(3 >= $z && $z >= 2){
									$u = 'table-warning';
								} elseif($z < 2){
									$u = 'table-danger';
								} else {
									$u = NULL;
								}
							?>
							@if($ul == $us)
								<tr class="{{ $u }}" >
									<td>{{ $a->belongstostaffleave->belongstostaff?->name }}</td>
									<td>{{ $a->belongstostaffleave->belongstooptleavetype?->leave_type_code }}</td>
									<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $a->belongstostaffleave->reason }}">{{ str($a->belongstostaffleave->reason)->words(3, ' >') }}</td>
									<td>{{ $dts }}</td>
									<td>{{ $dte }}</td>
									<td>{{ $dper }}</td>
									<td>
										<!-- Button trigger modal -->
										<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#hodapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>

										<!-- Modal for supervisor approval-->
										<div class="modal fade" id="hodapproval{{ $a->id }}" aria-labelledby="hodlabel{{ $a->id }}" aria-hidden="true">
										<!-- <div class="modal fade" id="hodapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
											<div class="modal-dialog modal-dialog-centered">
												<div class="modal-content">
													<div class="modal-header">
														<h1 class="modal-title fs-5" id="hodlabel{{ $a->id }}">Head of Department Approval</h1>
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
													</div>
													<div class="modal-body">
														{{ Form::open(['route' => ['leavestatus.hodstatus'], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true, 'data-toggle' => 'validator']) }}
														{{ Form::hidden('id', $a->id) }}
														@foreach($ls as $k => $val)
														<div class="form-check form-check-inline {{ $errors->has('leave_status_id') ? 'has-error' : '' }}">
															<input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="hodstatus{{ $a->id.$val['id'] }}" class="form-check-input">
															<label class="form-check-label" for="hodstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
														</div>
														@endforeach
														<div class="mb-3 row">
															<div class="form-group row {{ $errors->has('verify_code') ? 'has-error' : '' }}">
																<label for="hodcode{{ $val['id'] }}" class="col-auto col-form-label col-form-label-sm">Verify Code :</label>
																<div class="col-auto">
																	<input type="text" name="verify_code" value="{{ @$value }}" id="hodcode{{ $val['id'] }}" class="form-control form-control-sm" placeholder="Verify Code">
																</div>
															</div>
														</div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
														{{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
													</div>
														{{ Form::close() }}
												</div>
											</div>
										</div>

									</td>
								</tr>
							@endif
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	@endif

	<p>&nbsp;</p>
	@if($d1)
		@if(HRLeaveApprovalDirector::whereNull('leave_status_id')->get()->count())
			<div class="col-auto table-responsive">
				<h4>Director Approval</h4>
				<table class="table table-hover table-sm" id="sapprover" style="font-size:12px">
					<thead>
						<tr>
							<th rowspan="2">Name</th>
							<th rowspan="2">Leave</th>
							<th rowspan="2">Reason</th>
							<th colspan="2">Date/Time Leave</th>
							<th rowspan="2">Period</th>
							<th rowspan="2">Leave Status</th>
						</tr>
						<tr>
							<th>From</th>
							<th>To</th>
						</tr>
					</thead>
					<tbody>
						@foreach(HRLeaveApprovalDirector::whereNull('leave_status_id')->get() as $a)
							<?php
							$ul = $a->belongstostaffleave->belongstostaff->belongstomanydepartment->first()->branch_id;				//get user leave branch_id
							$us = \Auth::user()->belongstostaff->belongstomanydepartment->first()->branch_id;						//get user director branch_id
							// echo $ul.' | '.$us;

								if ( ($a->belongstostaffleave->leave_type_id == 9) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 2) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 1) ) {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y g:i a');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y g:i a');

									if ($a->belongstostaffleave->leave_type_id != 9) {
										if ($a->belongstostaffleave->half_type_id == 2) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										} elseif($a->belongstostaffleave->half_type_id == 1) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										}
									}elseif ($a->belongstostaffleave->leave_type_id == 9) {
										$i = \Carbon\Carbon::parse($a->belongstostaffleave->period_time);
										$dper = $i->hour.' hour, '.$i->minute.' minutes';
									}

								} else {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y ');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y ');
									$dper = $a->belongstostaffleave->period_day.' day/s';
								}
								$z = \Carbon\Carbon::parse(now())->daysUntil($a->belongstostaffleave->date_time_start, 1)->count();
								if(3 >= $z && $z >= 2){
									$u = 'table-warning';
								} elseif($z < 2){
									$u = 'table-danger';
								} else {
									$u = NULL;
								}
							?>
							<tr class="{{ $u }}" >
								<td>{{ $a->belongstostaffleave->belongstostaff?->name }}</td>
								<td>{{ $a->belongstostaffleave->belongstooptleavetype?->leave_type_code }}</td>
								<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $a->belongstostaffleave->reason }}">{{ str($a->belongstostaffleave->reason)->words(3, ' >') }}</td>
								<td>{{ $dts }}</td>
								<td>{{ $dte }}</td>
								<td>{{ $dper }}</td>
								<td>
									<!-- Button trigger modal -->
									<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#dirapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>

									<!-- Modal for supervisor approval-->
									<div class="modal fade" id="dirapproval{{ $a->id }}" aria-labelledby="dirlabel{{ $a->id }}" aria-hidden="true">
									<!-- <div class="modal fade" id="dirapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h1 class="modal-title fs-5" id="dirlabel{{ $a->id }}">Director Approval</h1>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
													{{ Form::open(['route' => ['leavestatus.dirstatus'], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true, 'data-toggle' => 'validator']) }}
													{{ Form::hidden('id', $a->id) }}
													@foreach($ls as $k => $val)
													<div class="form-check form-check-inline {{ $errors->has('leave_status_id') ? 'has-error' : '' }}">
														<input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="dirstatus{{ $a->id.$val['id'] }}" class="form-check-input">
														<label class="form-check-label" for="dirstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
													</div>
													@endforeach
													<div class="mb-3 row">
														<div class="form-group row {{ $errors->has('verify_code') ? 'has-error' : '' }}">
															<label for="hodcode{{ $val['id'] }}" class="col-auto col-form-label col-form-label-sm">Verify Code :</label>
															<div class="col-auto">
																<input type="text" name="verify_code" value="{{ @$value }}" id="hodcode{{ $val['id'] }}" class="form-control form-control-sm" placeholder="Verify Code">
															</div>
														</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
													{{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
												</div>
													{{ Form::close() }}
											</div>
										</div>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	@endif

	<p>&nbsp;</p>
	@if($r1)
		@if(HRLeaveApprovalHR::whereNull('leave_status_id')->get()->count())
			<div class="col-auto table-responsive">
				<h4>Director Approval</h4>
				<table class="table table-hover table-sm" id="sapprover" style="font-size:12px">
					<thead>
						<tr>
							<th rowspan="2">Name</th>
							<th rowspan="2">Leave</th>
							<th rowspan="2">Reason</th>
							<th colspan="2">Date/Time Leave</th>
							<th rowspan="2">Period</th>
							<th rowspan="2">Leave Status</th>
						</tr>
						<tr>
							<th>From</th>
							<th>To</th>
						</tr>
					</thead>
					<tbody>
						@foreach(HRLeaveApprovalHR::whereNull('leave_status_id')->get() as $a)
							<?php
							$ul = $a->belongstostaffleave->belongstostaff->belongstomanydepartment->first()->branch_id;				//get user leave branch_id
							$us = \Auth::user()->belongstostaff->belongstomanydepartment->first()->branch_id;						//get user hr branch_id
							// echo $ul.' | '.$us;

								if ( ($a->belongstostaffleave->leave_type_id == 9) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 2) || ($a->belongstostaffleave->leave_type_id != 9 && $a->belongstostaffleave->half_type_id == 1) ) {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y g:i a');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y g:i a');

									if ($a->belongstostaffleave->leave_type_id != 9) {
										if ($a->belongstostaffleave->half_type_id == 2) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										} elseif($a->belongstostaffleave->half_type_id == 1) {
											$dper = $a->belongstostaffleave->period_day.' Day';
										}
									}elseif ($a->belongstostaffleave->leave_type_id == 9) {
										$i = \Carbon\Carbon::parse($a->belongstostaffleave->period_time);
										$dper = $i->hour.' hour, '.$i->minute.' minutes';
									}

								} else {
									$dts = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_start)->format('j M Y ');
									$dte = \Carbon\Carbon::parse($a->belongstostaffleave->date_time_end)->format('j M Y ');
									$dper = $a->belongstostaffleave->period_day.' day/s';
								}
								$z = \Carbon\Carbon::parse(now())->daysUntil($a->belongstostaffleave->date_time_start, 1)->count();
								if(3 >= $z && $z >= 2){
									$u = 'table-warning';
								} elseif($z < 2){
									$u = 'table-danger';
								} else {
									$u = NULL;
								}
							?>
							<tr class="{{ $u }}" >
								<td>{{ $a->belongstostaffleave->belongstostaff?->name }}</td>
								<td>{{ $a->belongstostaffleave->belongstooptleavetype?->leave_type_code }}</td>
								<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $a->belongstostaffleave->reason }}">{{ str($a->belongstostaffleave->reason)->words(3, ' >') }}</td>
								<td>{{ $dts }}</td>
								<td>{{ $dte }}</td>
								<td>{{ $dper }}</td>
								<td>
									<!-- Button trigger modal -->
									<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#hrapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>

									<!-- Modal for supervisor approval-->
									<div class="modal fade" id="hrapproval{{ $a->id }}" aria-labelledby="hrlabel{{ $a->id }}" aria-hidden="true">
									<!-- <div class="modal fade" id="hrapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h1 class="modal-title fs-5" id="hrlabel{{ $a->id }}">Human Resource Department Approval</h1>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
													{{ Form::open(['route' => ['leavestatus.hrstatus'], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true, 'data-toggle' => 'validator']) }}
													{{ Form::hidden('id', $a->id) }}
													@foreach($ls as $k => $val)
													<div class="form-check form-check-inline {{ $errors->has('leave_status_id') ? 'has-error' : '' }}">
														<input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="hrstatus{{ $a->id.$val['id'] }}" class="form-check-input">
														<label class="form-check-label" for="hrstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
													</div>
													@endforeach
													<div class="mb-3 row">
														<div class="form-group row {{ $errors->has('verify_code') ? 'has-error' : '' }}">
															<label for="hodcode{{ $val['id'] }}" class="col-auto col-form-label col-form-label-sm">Verify Code :</label>
															<div class="col-auto">
																<input type="text" name="verify_code" value="{{ @$value }}" id="hodcode{{ $val['id'] }}" class="form-control form-control-sm" placeholder="Verify Code">
															</div>
														</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
													{{ Form::submit('Submit', ['class' => 'btn btn-sm btn-outline-secondary']) }}
												</div>
													{{ Form::close() }}
											</div>
										</div>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	@endif

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
	"columnDefs": [ { type: 'date', 'targets': [4] } ],
	"order": [[0, "asc" ]],	// sorting the 6th column descending
	responsive: true
});

$('#bapprover, #sapprover, #hodapprover, #dirapprover, #hrapprover').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"order": [[3, "asc" ]],	// sorting the 4th column descending
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
// replacement approve leave
$(document).on('click', '.rapprover_btn', function(e){
	var ackID = $(this).data('id');
	SwalDeleteR(ackID);
	e.preventDefault();
});

function SwalDeleteR(ackID){
	swal.fire({
		title: 'Approve Leave',
		text: 'Are you sure to approve this leave?',
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
					url: '{{ url('leaverapprove') }}' + '/' + ackID,
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

