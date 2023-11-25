@extends('layouts.app')

@section('content')
<?php
// load models
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\HRLeaveReplacement;
use App\Models\HumanResources\HRLeaveApprovalBackup;
use App\Models\HumanResources\HRLeaveApprovalSupervisor;
use App\Models\HumanResources\HRLeaveApprovalHOD;
use App\Models\HumanResources\HRLeaveApprovalDirector;
use App\Models\HumanResources\HRLeaveApprovalHR;
use App\Models\HumanResources\OptLeaveStatus;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load sql builder
use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;

use \App\Helpers\UnavailableDateTime;

// who am i?
$user = \Auth::user()->belongstostaff;
$me1 = $user->div_id == 1;		// hod
$me2 = $user->div_id == 5;		// hod assistant
$me3 = $user->div_id == 4;		// supervisor
$me4 = $user->div_id == 3;		// HR
$me5 = $user->authorise_id == 1;	// admin
$me6 = $user->div_id == 2;		// director
$dept = $user->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;

$s1 = $me3 || (($me1 || $me2) && $user->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14) || $me5;	// supervisor and hod HR
$h1 = $me1 || (($me1 || $me2) && $user->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14) || $me5;	// HOD and hod HR
$d1 = $me6 || ($me1 && $user->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14) || $me5;	// dir and hod HR
$r1 = (($me1 || $me2) && $user->belongstomanydepartment()->wherePivot('main', 1)->first()->department_id == 14) || $me5;													// hod HR


// for supervisor and hod approval
// $ls['results'] = [];
if($me6) {																			// only director
	$c = OptLeaveStatus::whereIn('id', [4,5,6])->get();								// only rejected, approve and waived
} else {
	$c = OptLeaveStatus::whereIn('id', [4,5])->get();								// only rejected and approve
}
foreach ($c as $v) {
	$ls[] = ['id' => $v->id, 'text' => $v->status];
}
?>
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	@if($h1)
		@if(HRLeaveApprovalHOD::whereNull('leave_status_id')->get()->count())
			<div class="col-sm-12 table-responsive">
				<h4>Head Of Department Approval</h4>
				<table class="table table-hover table-sm" id="sapprover" style="font-size:11px">
					<thead>
						<tr>
							<th rowspan="2">Leave ID</th>
							<th rowspan="2">ID</th>
							<th rowspan="2">Name</th>
							<th rowspan="2">Leave</th>
							<th rowspan="2">Reason</th>
							<th rowspan="2">Date Applied</th>
							<th colspan="2">Date/Time Leave</th>
							<th rowspan="2">Period</th>
							<th rowspan="2">Backup Status</th>
							<th rowspan="2">Approval</th>
						</tr>
						<tr>
							<th>From</th>
							<th>To</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$dept = $user->belongstomanydepartment()->wherePivot('main', 1)->first();
						$deptid = $dept->id;
						$branch = $dept->branch_id;
						$category = $dept->category_id;
						$i = 1;
						// dump($deptid, $branch, $category);
						?>
						@foreach(HRLeaveApprovalHOD::whereNull('leave_status_id')->get() as $a)
							<?php
							$leav = HRLeave::find($a->leave_id);
							$staff = Staff::find($leav->staff_id);
							// dump($staff);
							$sta = $staff->belongstomanydepartment()->wherePivot('main', 1)->first();
							$stadept = $sta->id;
							$stacate = $sta->category_id;
							// dd($stadept, $stacate);

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
							$z = \Carbon\Carbon::parse(now())->daysUntil($leav->date_time_start, 1)->count();
							if(3 >= $z && $z >= 2){
								$u = 'table-warning';
							} elseif($z < 2){
								$u = 'table-danger';
							} else {
								$u = NULL;
							}

							// find leave backup if any
							$backup = $leav->hasmanyleaveapprovalbackup()->get();
							if ($backup->count()) {
								if (is_null($backup->first()->leave_status_id)) {
									$bapp = 'Pending';
								} else {
									$bapp = OptLeaveStatus::find($backup->first()->leave_status_id)->status;
								}
							} else {
								$bapp = 'No Backup';
							}

							// find leave supervisor if any
							$supervisor = $leav->hasmanyleaveapprovalsupervisor()->get();
							if ($supervisor->count()) {
								if (is_null($supervisor->first()->leave_status_id)) {
									$supp = 'Pending';
								} else {
									$supp = OptLeaveStatus::find($supervisor->first()->leave_status_id)->status;
								}
							} else {
								$supp = 'No Supervisor';
							}
							?>
							@if($me5)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@if($deptid == 28 || $deptid == 21)				<!-- production manager -->
								@if($stadept == 2 || $stadept == 3 || $stadept == 4 || $stadept == 8 || $stadept == 18 || $stadept == 19 || $stadept == 20 || $stadept == 25 || $stadept == 27 || $stadept == 30 || $staff->div_id == 4 || $stadept == 21 || $stadept == 28)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 6)				<!-- cs office -->
								@if($stadept == 6 || $stadept == 7 || $stadept == 3)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 23)				<!-- purchasing -->
								@if($stadept == 23 || $stadept == 17 || $stadept == 11 || $stadept == 16)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 1)				<!-- account -->
								@if($stadept == 1)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 5)				<!-- costing -->
								@if($stadept == 5)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 12)				<!-- engineering -->
								@if($stadept == 12)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 14)				<!-- hr -->
								@if($stadept == 14)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 15)				<!-- it -->
								@if($stadept == 15)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 22)				<!-- plc programmer -->
								@if($stadept == 22)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
							@if($deptid == 24)				<!-- Sales -->
								@if($stadept == 24)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}">HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()->where('active', 1)->first()->username }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $staff->name }}">
											{{ str($leav->belongstostaff?->name)->words(3, ' >') }}
										</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{{ $bapp }}</td>
										<td>
											<!-- Button trigger modal -->
											@if($backup->count())
												@if(!is_null($backup->first()->leave_status_id))
													<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
												@endif
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sapproval{{ $a->id }}" data-id="{{ $a->id }}"><i class="bi bi-box-arrow-in-down"></i></button>
											@endif

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
							@endif
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
// tooltip
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});

/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#bapprover, #sapprover, #hodapprover, #dirapprover, #hrapprover').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [ { type: 'date', 'targets': [5,6,7] } ],
	"order": [[6, "desc" ]],	// sorting the 4th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);
@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection
