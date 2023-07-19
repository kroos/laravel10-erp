@extends('layouts.app')

@section('content')
<?php
// 1st sekali check profile. checking utk email & emergency person. lock kat sini smpi user isi baru buleh apply cuti.

// check emergency person
$us = \Auth::user()->belongstostaff;
$emer = $us->hasmanyemergency()->get();

// check email
// dd ($us->email);
$email = $us->email;
$e =  $us->hasmanyemergency()->get();
$leaveALMC =  $us->hasmanyleaveentitlement()->where('year', date('Y'))->first();
// $leaveALMC =  $us->hasmanyleaveentitlements()->whereFirst('year', date('Y'));
// dd($leaveALMC);
?>

		<dl class="row">
			<dt class="col-sm-3"><h5 class="text-danger">Attention :</h5></dt>
			<dd class="col-sm-9">
				<p>Please complete your profile before applying your leave.<br />
					@if(is_null($email) && is_null($emer) && $e->count() == 0)
						<a href="{{ route('profile.edit',  $us->id ) }}" class="btn btn-sm btn-outline-secondary" >Profile</a>
					@else
						<a href="{{ route('leave.create' ) }}" class="btn btn-sm btn-outline-secondary" >Leave Application</a>
					@endif
				</p>
			</dd>

			<dt class="col-sm-3"><h5>Annual Leave :</h5></dt>
			<dd class="col-sm-9">
				<dl class="row">
					<dt class="col-sm-3">Initialize : </dt>
					<dd class="col-sm-9">{{ $leaveALMC->al_initialise + $leaveALMC->al_adjustment }} days</dd>
					<dt class="col-sm-3">Balance :</dt>
					<dd class="col-sm-9"><span class=" {{ ($leaveALMC->al_balance < 4)?'text-danger font-weight-bold':'' }}">{{ $leaveALMC->al_balance }} days</span>
					</dd>
				</dl>
			</dd>

			<dt class="col-sm-3"><h5>MC Leave :</h5></dt>
			<dd class="col-sm-9">
				<dl class="row">
					<dt class="col-sm-3">Initialize :</dt>
					<dd class="col-sm-9">{{ $leaveALMC->mc_initialise + $leaveALMC->mc_adjustment }} days</dd>
					<dt class="col-sm-3">Balance :</dt>
					<dd class="col-sm-9"><span class=" {{ ($leaveALMC->medical_leave_balance < 4)?'text-danger font-weight-bold':'' }}">{{ $leaveALMC->mc_balance }} days</span></dd>
				</dl>
			</dd>

			@if( $us->gender_id == 2 )
				<dt class="col-sm-3 text-truncate"><h5>Maternity Leave :</h5></dt>
				<dd class="col-sm-9">
					<dl class="row">
						<dt class="col-sm-3">Initialize :</dt>
						<dd class="col-sm-9">{{ $leaveALMC->maternity_initialise + $leaveALMC->maternity_adjustment }} days</dd>
						<dt class="col-sm-3">Balance :</dt>
						<dd class="col-sm-9"><span class=" {{ ($leaveALMC->maternity_leave_balance < 4)?'text-danger font-weight-bold':'' }}">{{ $leaveALMC->maternity_leave_balance }} days</span></dd>
					</dl>
				</dd>
			@endif
			<dt class="col-sm-3"><h5>Unpaid Leave Utilize :</h5></dt>
			<dd class="col-sm-9">{{  $us->hasmanyleave()->whereYear( 'date_time_start', date('Y') )->whereIn('leave_type_id', [3, 6])->get()->sum('period_day') }} days</dd>
			@if($us->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->get()->sum('leave_balance') > 0)
				<dt class="col-sm-3"><h5>Replacement Leave :</h5></dt>
				<dd class="col-sm-9">{{ $oi->sum('leave_balance') }} days</dd>
			@endif

			@if($us->belongstoleaveapprovalflow->backup_approval == 1)
				<dt class="col-sm-3"><h5>Backup Personnel :</h5></dt>
				<dd class="col-sm-9">
				<?php
				// find backup person according to its department
				// need to get the department 1st
				$dept = $us->belongstomanydepartment()->get();
				?>
					<ul>
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
					</ul>
				</dd>
			@endif
		</dl>


<!-- list of leaves -->
<?php
// dd(\Carbon\Carbon::now()->copy()->startOfYear());
$starty = \Carbon\Carbon::now()->copy()->startOfYear();
$lea =  $us->hasmanyleave()->where('date_time_start', '>=', $starty)->get();
// dd($lea);
?>
@if( $lea->count() > 0 )
		<table class="table table-hover table-sm" id="leaves" style="font-size:10px">
			<thead>
				<tr>
					<th rowspan="2">ID</th>
					<th rowspan="2">Date Apply</th>
					<th rowspan="2">Leave</th>
					<th rowspan="2">Reason</th>
					<th colspan="2">Date/Time Leave</th>
					<th rowspan="2">Period</th>
					<th rowspan="2">Approval, Remarks and Updated At</th>
					<th rowspan="2">Remarks</th>
					<th rowspan="2">Leave Status</th>
				</tr>
				<tr>
					<th>From</th>
					<th>To</th>
				</tr>
			</thead>
			<tbody>
@foreach($lea as $leav)
				<tr>
					<td>
<?php
$dts = \Carbon\Carbon::parse($leav->date_time_start)->format('Y');
$arr = str_split( $dts, 2 );
?>
						HR9-{{ str_pad( $leav->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $arr[1] }}
							<br />
						<a href="#" class="btn btn-primary" alt="Print PDF" title="Print PDF" target="_blank"><i class="far fa-file-pdf"></i></a>
<?php
 // only available if only now is before date_time_start and active is 1
$dtsl = \Carbon\Carbon::parse( $leav->date_time_start );
$dt = \Carbon\Carbon::now()->lte( $dtsl );
?>
@if( $leav->status == 1 && $dt == 1 )
						<a href="{{ __('route') }}" class="btn btn-primary cancel_btn" id="cancel_btn_{{ $leav->id }}" data-id="{{ $leav->id }}" alt="Cancel" title="Cancel"><i class="fas fa-ban"></i></a>
@endif
					</td>
					<td>{{ \Carbon\Carbon::parse($leav->created_at)->format('D, j F Y') }}</td>
					<td>{{ $leav->belongstooptleave->leave }}</td>
					<td>{{ $leav->reason }}</td>
					<td>{{ $dts }}</td>
					<td>{{ $dte }}</td>
					<td>{{ $dper	}}</td>
<!-- @if( ($us->ergroup->category_id == 1 || $us->ergroup->group_id == 5 || $us->ergroup->group_id == 6) || $us->erneedbackup == 1 ) -->
					<td>{{ $officer }}</td>
					<td>{{ $stat }}</td>
<!-- @endif -->
					<td>
					</td>
					<td>{{ $leav->remarks }}</td>
					<td>{{ $leav->belongtoleavestatus->status }}</td>
				</tr>
@endforeach
			</tbody>
		</table>

@else
		<p class="card-text text-justify text-lead">Sorry, no record for your leave. Click on "Leave Application" to apply a leave.</p>
@endif

	</div>
	<div class="card-footer justify-content-center">
<?php
$w =  $us->gender_id;
$r =  $us->mobile;
$e =  $us->hasmanyemergency()->get();
?>
		<a href="{{ ( is_null($w) && is_null($r) && $e->count() == 0)?route('profile.edit', \Auth::user()->belongstostaff->id):route('leave.create') }}" class="btn btn-sm btn-outline-secondary">{{ ( is_null($w) || is_null($r) || $e->count() == 0)?'Profile':'Leave Application' }}</a>
	</div>
</div>





@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
@endsection