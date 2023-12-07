@extends('layouts.app')

@section('content')
<?php
// load models
use App\Models\Staff;
use App\Models\Login;
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
use App\Models\HumanResources\HRAttendance;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load sql builder
use Illuminate\Database\Eloquent\Builder;

use \Carbon\Carbon;
use \Carbon\CarbonPeriod;

use \App\Helpers\UnavailableDateTime;

$auth = \Auth::user()->belongstostaff?->div_id; // 1/2/5
$auth_dept = \Auth::user()->belongstostaff?->belongstomanydepartment()->first()->id; // 14/31
$auth_admin = \Auth::user()->belongstostaff?->authorise_id; // 1

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

// filtering the view
$us = $user->belongstomanydepartment->first()?->branch_id;							//get user supervisor branch_id
?>
<style>
	@media print {
		body {
			visibility: hidden;
		}

		#printPageButton, #back {
			display: none;
		}

		.table-container {
			visibility: visible;
			position: absolute;
			left: 0;
			top: 0;
		}
	}

	.table-container {
		display: table;
		width: 100%;
		border-collapse: collapse;
	}

	.table {
		display: table;
		width: 100%;
		border-collapse: collapse;
		margin-top: 0;
		padding-top: 0;
		margin-bottom: 0;
		padding-bottom: 0;
	}

	.table-row {
		display: table-row;
	}

	.table-cell {
		display: table-cell;
		border: 1px solid #b3b3b3;
		padding: 4px;
		box-sizing: border-box;
	}

	.table-cell-top {
		display: table-cell;
		border: 1px solid #b3b3b3;
		border-top: none;
		padding: 4px;
		box-sizing: border-box;
	}

	.table-cell-top-bottom {
		display: table-cell;
		border: 1px solid #b3b3b3;
		border-top: none;
		border-bottom: none;
		padding: 0px;
		box-sizing: border-box;
	}

	.table-cell-hidden {
		display: table-cell;
		border: none;
	}

	.header {
		font-size: 22px;
		text-align: center;
	}

	.theme {
		background-color: #e6e6e6;
	}

	.table-cell-top1 {
		display: table-cell;
		border: 1px solid #b3b3b3;
		border-top: none;
		padding: 0px;
		box-sizing: border-box;
	}
</style>

<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	@if($s1)
		@if(HRLeaveApprovalSupervisor::whereNull('leave_status_id')->get()->count())
			<div class="col-sm-12 table-responsive">
				<h4>Supervisor Approval</h4>
				<table class="table table-hover table-sm" id="sapprover" style="font-size:12px">
					<thead>
						<tr>
							<th rowspan="2">ID Leave</th>
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
						@foreach(HRLeaveApprovalSupervisor::whereNull('leave_status_id')->get() as $a)
							<?php
							$count = 0;
							$supervisor_no = 0;
							$hod_no = 0;
							$director_no = 0;
							$hr_no = 0;
							$leav = HRLeave::find($a->leave_id);
							$staff1 = Staff::find($leav->staff_id);
							$ul = $leav?->belongstostaff?->belongstomanydepartment->first()?->branch_id;				//get user leave branch_id
							$udept = $leav?->belongstostaff?->belongstomanydepartment->first()?->id;
							// echo $us.' | '.$ul.'<br />';
							// dd($a);

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
									$bapp = '<span class="text-danger">Pending</span>';
									$bappb = false;
								} else {
									$bapp = '<span class="text-success">'.OptLeaveStatus::find($backup->first()->leave_status_id)->status.'</span>';
									$bappb = true;
								}
							} else {
								$bapp = '<span class="text-success">No Backup</span>';
									$bappb = true;
							}

							$hrremarksattendance = HRAttendance::where(function (Builder $query) use ($leav){
																	$query->whereDate('attend_date', '>=', $leav->date_time_start)
																	->whereDate('attend_date', '<=', $leav->date_time_end);
																})
													->where('staff_id', $leav->staff_id)
													->where(function (Builder $query) {
														$query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
													})
													// ->ddrawsql();
													->get();
							$supervisor = $leav->hasmanyleaveapprovalsupervisor?->first();
							$hod = $leav->hasmanyleaveapprovalhod?->first();
							$director = $leav->hasmanyleaveapprovaldir?->first();
							$hr = $leav->hasmanyleaveapprovalhr?->first();
							// entitlement
							$annl = $staff1->hasmanyleaveannual()?->where('year', now()->format('Y'))->first();
							$mcel = $staff1->hasmanyleavemc()?->where('year', now()->format('Y'))->first();
							$matl = $staff1->hasmanyleavematernity()?->where('year', now()->format('Y'))->first();
							$replt = $staff1->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')->where(function(Builder $query){$query->whereDate('date_start', '>=', now()->startOfYear())->whereDate('date_end', '<=', now()->endOfYear());})->get();
							$replb = $staff1->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')->where(function(Builder $query){$query->whereDate('date_start', '>=', now()->startOfYear())->whereDate('date_end', '<=', now()->endOfYear());})->get();
							$upal = $staff1->hasmanyleave()?->selectRaw('SUM(period_day) as total')
															->where(function(Builder $query){
																$query->whereDate('date_time_start', '>=', now()->startOfYear())
																	->whereDate('date_time_end', '<=', now()->endOfYear());
																})
															->where(function(Builder $query) {
																$query->whereIn('leave_status_id', [5,6])
																	->orWhereNull('leave_status_id');
															})
															->whereIn('leave_type_id', [3, 6])
															->get();
							$mcupl = $staff1->hasmanyleave()?->selectRaw('SUM(period_day) as total')
															->where(function(Builder $query){
																$query->whereDate('date_time_start', '>=', now()->startOfYear())
																	->whereDate('date_time_end', '<=', now()->endOfYear());
																})
															->where(function(Builder $query) {
																$query->whereIn('leave_status_id', [5,6])
																	->orWhereNull('leave_status_id');
															})
															->where('leave_type_id', 11)
															->get();
							$mcupl = $staff1->hasmanyleave()?->get();
							?>
							@if($me3)
								@if($ul == $us)
									<tr class="{{ $u }}" >
										<td>
											<a href="{{ route('leave.show', $a->leave_id) }}" >HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
										</td>
										<td>{{ $leav->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username }}</td>
										<td>{{ $leav->belongstostaff?->name }}</td>
										<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
										<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
											{{ Str::limit($leav->reason, 7, ' >') }}
										</td>
										<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
										<td>{{ $dts }}</td>
										<td>{{ $dte }}</td>
										<td>{{ $dper }}</td>
										<td>{!! $bapp !!}</td>
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
											<div class="modal fade" id="sapproval{{ $a->id }}" aria-labelledby="suplabel{{ $a->id }}" aria-hidden="true">
											<!-- <div class="modal fade" id="sapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
												<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
													<div class="modal-content">
														<div class="modal-header">
															<h1 class="modal-title fs-5" id="suplabel{{ $a->id }}">Supervisor Approval</h1>
															<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body align-items-start justify-content-center">
<!-- LEAVE SHOW START -->




															<div class="col-sm-12">
																<div class="table-container">
																	<div class="table">
																		<div class="table-row header">
																			<div class="table-cell" style="width: 40%; background-color: #99ff99;">IPMA INDUSTRY SDN.BHD.</div>
																			<div class="table-cell" style="width: 60%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top" style="width: 25%;">STAFF ID : {{ $leav->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username }}</div>
																			<div class="table-cell-top" style="width: 75%;">NAME : {{ $leav->belongstostaff?->name }}</div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top" style="width: 25%;">LEAVE NO : HR9-{{ @str_pad($leav->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $leav->leave_year }}</div>
																			<div class="table-cell-top" style="width: 60%;">DATE : {{ $dts }} - {{ $dte }} </div>
																			<div class="table-cell-top" style="width: 25%;">TOTAL : {{ $dper }} </div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ $leav->belongstooptleavetype->leave_type_code }} ({{ $leav->belongstooptleavetype->leave_type }})</div>
																			<div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ $leav->reason }} </div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {{ @$backup_name }}</div>
																			<div class="table-cell-top" style="width: 40%;">DATE APPROVED : {{ @$approved_date }} </div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top text-wrap" style="width: 60%;">AL : {{ $annl->first()?->annual_leave_balance }}/{{ $annl->first()?->annual_leave }}</div>
																			<div class="table-cell-top" style="width: 40%;">DATE APPROVED : {{ @$approved_date }} </div>
																		</div>
																	</div>

																	@if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
																		@if($hrremarksattendance)
																		<div class="table">
																			@foreach($hrremarksattendance as $key => $value)
																			<div class="table-row">
																				<div class="table-cell-top" style="width: 100%;">REMARKS FROM ATTENDANCE : {!! $value->remarks !!}<br/>HR REMARKS FROM ATTENDANCE : {!! $value->hr_remarks !!}</div>
																			</div>
																			@endforeach
																		</div>
																		@endif
																	@endif

																	@if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
																		@if($leav->remarks)
																		<div class="table">
																			<div class="table-row">
																				<div class="table-cell-top" style="width: 100%;">LEAVE REMARKS : {!! $leav->remarks !!}</div>
																			</div>
																		</div>
																		@endif
																	@endif

																	@if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
																		@if($leav->hasmanyleaveamend()->count())
																		<div class="table">
																			@foreach($leav->hasmanyleaveamend()->get() as $key => $value1)
																			<div class="table-row">
																				<div class="table-cell-top" style="width: 100%;">EDIT LEAVE REMARKS : {{ $value1->amend_note }} on {{ \Carbon\Carbon::parse($value1->created_at)->format('j M Y') }}</div>
																			</div>
																			@endforeach
																		</div>
																		@endif
																	@endif
																	<p>Supporting Document : {!! ($leav->softcopy)?'<a href="'.asset('storage/leaves/'.$leav->softcopy).'" target="_blank">Link</a>':null !!} </p>
																</div>
															</div>







<!-- LEAVE SHOW END -->
															{{ Form::open(['route' => ['leavestatus.supervisorstatus'], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
															{{ Form::hidden('id', $a->id) }}

															<div class="col-sm-12 row">
																@foreach($ls as $k => $val)
																<div class="form-check form-check-inline">
																	<input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="supstatus{{ $a->id.$val['id'] }}" class="form-check-input">
																	<label class="form-check-label" for="supstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
																</div>
																@endforeach

																<div class="mb-3 row">
																	<div class="form-group row {{ $errors->has('verify_code') ? 'has-error' : '' }}">
																		<label for="supcode{{ $a->id }}" class="col-auto col-form-label col-form-label-sm">Verify Code :</label>
																		<div class="col-auto">
																			<input type="text" name="verify_code" value="{{ (($user->div_id == 1 && $user->belongstomanydepartment->first()->id == 14) || $user->authorise_id == 1)?$leav->verify_code:@$value }}" id="supcode{{ $a->id }}" class="form-control form-control-sm" placeholder="Verify Code">
																		</div>
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
							@else
								<tr class="{{ $u }}" >
									<td>
										<a href="{{ route('leave.show', $a->leave_id) }}" >HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leav->leave_year }}</a>
									</td>
									<td>{{ $leav->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username }}</td>
									<td>{{ $leav->belongstostaff?->name }}</td>
									<td>{{ $leav->belongstooptleavetype?->leave_type_code }}</td>
									<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $leav->reason }}">
										{{ Str::limit($leav->reason, 7, ' >') }}
									</td>
									<td>{{ Carbon::parse($a->created_at)->format('j M Y') }}</td>
									<td>{{ $dts }}</td>
									<td>{{ $dte }}</td>
									<td>{{ $dper }}</td>
									<td>{!! $bapp !!}</td>
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
										<div class="modal fade" id="sapproval{{ $a->id }}" aria-labelledby="suplabel{{ $a->id }}" aria-hidden="true">
										<!-- <div class="modal fade" id="sapproval{{ $a->id }}" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"> -->
											<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
												<div class="modal-content">
													<div class="modal-header">
														<h1 class="modal-title fs-5" id="suplabel{{ $a->id }}">Supervisor Approval</h1>
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
													</div>
													<div class="modal-body align-items-start justify-content-center">

<!-- LEAVE SHOW START -->
															<div class="col-sm-12 row">
																<div class="table-container">
																	<div class="table">
																		<div class="table-row header">
																			<div class="table-cell" style="width: 40%; background-color: #99ff99;">IPMA INDUSTRY SDN.BHD.</div>
																			<div class="table-cell" style="width: 60%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top" style="width: 25%;">STAFF ID : {{ $leav->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username }}</div>
																			<div class="table-cell-top" style="width: 75%;">NAME : {{ $leav->belongstostaff?->name }}</div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top" style="width: 25%;">LEAVE NO : HR9-{{ @str_pad($leav->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $leav->leave_year }}</div>
																			<div class="table-cell-top" style="width: 60%;">DATE : {{ $dts }} - {{ $dte }} </div>
																			<div class="table-cell-top" style="width: 25%;">TOTAL : {{ $dper }} </div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ $leav->belongstooptleavetype->leave_type_code }} ({{ $leav->belongstooptleavetype->leave_type }})</div>
																			<div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ $leav->reason }} </div>
																		</div>
																	</div>

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {!! $bapp !!}</div>
																			<div class="table-cell-top" style="width: 40%;">
																				BACKUP DATE APPROVED : {{ ($backup->first()?->created_at)?Carbon::parse($backup->first()?->created_at)->format('j M Y'):null }}
																			</div>
																		</div>
																	</div>

																	@if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
																		@if($hrremarksattendance)
																		<div class="table">
																			@foreach($hrremarksattendance as $key => $value)
																			<div class="table-row">
																				<div class="table-cell-top" style="width: 100%;">REMARKS FROM ATTENDANCE : {!! $value->remarks !!}<br/>HR REMARKS FROM ATTENDANCE : {!! $value->hr_remarks !!}</div>
																			</div>
																			@endforeach
																		</div>
																		@endif
																	@endif

																	@if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
																		@if($leav->remarks)
																		<div class="table">
																			<div class="table-row">
																				<div class="table-cell-top" style="width: 100%;">LEAVE REMARKS : {!! $leav->remarks !!}</div>
																			</div>
																		</div>
																		@endif
																	@endif

																	@if ((in_array($auth, ['1', '2', '5']) && in_array($auth_dept, ['14', '31'])) || $auth_admin == '1')
																		@if($leav->hasmanyleaveamend()->count())
																		<div class="table">
																			@foreach($leav->hasmanyleaveamend()->get() as $key => $value1)
																			<div class="table-row">
																				<div class="table-cell-top" style="width: 100%;">EDIT LEAVE REMARKS : {{ $value1->amend_note }} on {{ \Carbon\Carbon::parse($value1->created_at)->format('j M Y') }}</div>
																			</div>
																			@endforeach
																		</div>
																		@endif
																	@endif

																	<div class="table">
																		<div class="table-row">
																			<div class="table-cell-top text-wrap" style="width: 17%;">AL : {{ $annl?->first()?->annual_leave_balance }}/{{ $annl?->first()?->annual_leave }}</div>
																			<div class="table-cell-top text-wrap" style="width: 17%;">MC : {{ $mcel?->first()?->mc_leave_balance }}/{{ $mcel?->first()?->mc_leave }}</div>
																			<div class="table-cell-top text-wrap" style="width: 17%;">Maternity : {{ $matl?->first()?->maternity_leave_balance }}/{{ $matl?->first()?->maternity_leave }}</div>
																			<div class="table-cell-top text-wrap" style="width: 17%;">Replacement : {{ $replb?->first()?->total }}/{{ $replt?->first()?->total }}</div>
																			<div class="table-cell-top text-wrap" style="width: 17%;">UPL : {{ $upal?->first()?->total }}</div>
																			<div class="table-cell-top text-wrap" style="width: 15%;">MC-UPL : {{ $mcupl?->first()?->total }}</div>
																		</div>
																	</div>

																	<p>Supporting Document : {!! ($leav->softcopy)?'<a href="'.asset('storage/leaves/'.$leav->softcopy).'" target="_blank">Link</a>':null !!} </p>
																</div>
															</div>








<!-- LEAVE SHOW END -->
														{{ Form::open(['route' => ['leavestatus.supervisorstatus'], 'method' => 'patch', 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
														{{ Form::hidden('id', $a->id) }}

														@foreach($ls as $k => $val)
															<div class="form-check form-check-inline">
																<input type="radio" name="leave_status_id" value="{{ $val['id'] }}" id="supstatus{{ $a->id.$val['id'] }}" class="form-check-input">
																<label class="form-check-label" for="supstatus{{ $a->id.$val['id'] }}">{{ $val['text'] }}</label>
															</div>
														@endforeach

														<div class="form-group row mb-3 {{ $errors->has('verify_code') ? 'has-error' : '' }}">
															<label for="supcode{{ $a->id }}" class="col-sm-4 col-form-label col-form-label-sm">Verify Code :</label>
															<div class="col-sm-8">
																<input type="text" name="verify_code" value="{{ (($user->div_id == 1 && $user->belongstomanydepartment->first()->id == 14) || $user->authorise_id == 1)?$leav->verify_code:@$value }}" id="supcode{{ $a->id }}" class="form-control form-control-sm" placeholder="Verify Code">
															</div>
														</div>
														<div class="form-group row mb-3 {{ $errors->has('remarks') ? 'has-error' : '' }}">
															<label for="remarks{{ $a->id }}" class="col-sm-4 col-form-label col-form-label-sm">Remarks :</label>
															<div class="col-sm-8">
																<input type="textarea" name="remarks" value="{{ $a->remarks }}" id="remarks{{ $a->id }}" class="form-control form-control-sm" rows="3" placeholder="Remarks">
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
