@extends('layouts.app')

@section('content')
<?php
use \Carbon\Carbon;
?>

<div class="col-sm-12 row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h4 class="align-items-center">Profile {{ $staff->name }}
		<a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-outline-secondary">
			<i class="bi bi-person-lines-fill"></i> Edit
		</a>
		&nbsp;
		<a href="#" class="btn btn-sm btn-outline-secondary text-danger deactivate" data-id="{{ $staff->id }}">
			<i class="bi bi-person-fill-dash"></i> Deactivate
		</a>
	</h4>
	<div class="d-flex flex-column align-items-center text-center p-3 py-5">
		<img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $staff->image) }}">
		<span class="font-weight-bold">{{ $staff->name }}</span>
		<span class="font-weight-bold">{{ $staff->hasmanylogin()->where('active', 1)->first()?->username }}</span>
		<span> </span>
	</div>
	<div class="row align-items-start justify-content-center">
		<div class="col-sm-6 row gy-1 gx-1 align-items-start">
			<div class="col-5" style="background-color: #f2f2f2;">Name :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->name }}</div>
			<div class="col-5">Identity Card/Passport :</div>
			<div class="col-7">{{ $staff->ic }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Religion :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->belongstoreligion?->religion }}</div>
			<div class="col-5">Gender :</div>
			<div class="col-7">{{ $staff->belongstogender?->gender }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Race :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->belongstorace?->race }}</div>
			<div class="col-5">Nationality :</div>
			<div class="col-7">{{ $staff->belongstonationality?->country }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Marital Status :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->belongstomaritalstatus?->marital_status }}</div>
			<div class="col-5">Email :</div>
			<div class="col-7">{{ $staff->email }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Address :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->address }}</div>
			<div class="col-5">Place of Birth :</div>
			<div class="col-7">{{ $staff->place_of_birth }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Mobile :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->mobile }}</div>
			<div class="col-5">Phone :</div>
			<div class="col-7">{{ $staff->phone }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Date of Birth :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ \Carbon\Carbon::parse($staff->dob)->format('j M Y') }}</div>
			<div class="col-5">CIMB Account :</div>
			<div class="col-7">{{ $staff->cimb_account }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">EPF Account :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->epf_account }}</div>
			<div class="col-5">Income Tax No :</div>
			<div class="col-7">{{ $staff->income_tax_no }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">SOCSO No :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->socso_no }}</div>
			<div class="col-5">Weight :</div>
			<div class="col-7">{{ $staff->weight }} kg</div>
			<div class="col-5" style="background-color: #f2f2f2;">Height :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ $staff->height }} cm</div>
			<div class="col-5">Date Join :</div>
			<div class="col-7">{{ \Carbon\Carbon::parse($staff->join)->format('j M Y') }}</div>
			<div class="col-5" style="background-color: #f2f2f2;">Date Confirmed :</div>
			<div class="col-7" style="background-color: #f2f2f2;">{{ \Carbon\Carbon::parse($staff->confirmed)->format('j M Y') }}</div>
			<div class="col-5">Spouse :</div>
			<div class="col-7">
				@if($staff->hasmanyspouse()?->get()->count())
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th>Name</th>
							<th>Phone</th>
						</tr>
					</thead>
					<tbody>
					@foreach($staff->hasmanyspouse()?->get() as $sp)
						<tr>
							<td>{{ $sp->spouse }}</td>
							<td>{{ $sp->phone }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
				@endif
			</div>
			<div class="col-5" style="background-color: #f2f2f2;">Children :</div>
			<div class="col-7">
				@if($staff->hasmanychildren()?->get()->count())
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th>Name</th>
							<th>Age</th>
							<th>Tax Exemption (%)</th>
						</tr>
					</thead>
					<tbody>
					@foreach($staff->hasmanychildren()?->get() as $sc)
						<tr>
							<td>{{$sc->children}}</td>
							<td>{{ \Carbon\Carbon::parse($sc->dob)->toPeriod(now(), 1, 'year')->count() }} year/s</td>
							<td>{{ $sc->belongstotaxexemptionpercentage?->tax_exemption_percentage }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
				@endif
			</div>
			<div class="col-5">Emergency Contact :</div>
			<div class="col-7">
				@if($staff->hasmanyemergency()?->get()->count())
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th>Name</th>
							<th>Phone</th>
						</tr>
					</thead>
					<tbody>
					@foreach($staff->hasmanyemergency()?->get() as $sc)
						<tr>
							<td>{{ $sc->contact_person }}</td>
							<td>{{ $sc->phone }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
				@endif
			</div>
		</div>
		<div class="col-sm-6 row gy-1 gx-1 align-items-start">
			<div class="col-4" style="background-color: #f2f2f2;">System Administrator :</div>
			<div class="col-8" style="background-color: #f2f2f2;">{{ $staff->belongstoauthorised?->authorise }}</div>
			<div class="col-4">Staff Status :</div>
			<div class="col-8">{{ $staff->belongstostatus?->status }}</div>
			<div class="col-4" style="background-color: #f2f2f2;">Category :</div>
			<div class="col-8" style="background-color: #f2f2f2;">{{ $staff->belongstomanydepartment()?->wherePivot('main', 1)->first()->belongstocategory?->category }}</div>
			<div class="col-4">Branch :</div>
			<div class="col-8">{{ $staff->belongstomanydepartment()?->wherePivot('main', 1)->first()->belongstobranch?->location }}</div>
			<div class="col-4" style="background-color: #f2f2f2;">Department :</div>
			<div class="col-8" style="background-color: #f2f2f2;">{{ $staff->belongstomanydepartment()?->wherePivot('main', 1)->first()->department }}</div>
			<div class="col-4">Leave Approval Flow :</div>
			<div class="col-8">{{ $staff->belongstoleaveapprovalflow?->description }}</div>
			<div class="col-4" style="background-color: #f2f2f2;">RestDay Group :</div>
			<div class="col-8" style="background-color: #f2f2f2;">{{ $staff->belongstorestdaygroup?->group }}</div>
			<div class="col-4">Cross Backup To :</div>
			<?php
			$cb = $staff->crossbackupto()->get();
			?>
			<div class="col-8">
				@if($cb->count())
				<ul>
					@foreach($cb as $r)
					<li>{{ $r->name }}</li>
					@endforeach
				</ul>
				@endif
			</div>
			<div class="col-4" style="background-color: #f2f2f2;">Cross Backup For :</div>
			<?php
			$cbf = $staff->crossbackupfrom()->get();
			?>
			<div class="col-8">
				@if($cbf->count())
				<ul>
					@foreach($cbf as $rf)
					<li>{{ $rf->name }}</li>
					@endforeach
				</ul>
				@endif
			</div>
			@if($staff->hasmanyleaveannual()?->get()->count())
			<div class="col-4">Annual Leave :</div>
			<div class="col-8">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle" width="35px;">Year</th>
							<th class="text-center align-middle" width="98px;">AL Entitlement</th>
							<th class="text-center align-middle" width="95px;">AL Adjustment</th>
							<th class="text-center align-middle" width="68px;">AL Utilize</th>
							<th class="text-center align-middle">AL Balance</th>
							<th class="text-center align-middle" width="40px;">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					@foreach($staff->hasmanyleaveannual()->orderBy('year', 'DESC')->get() as $al)
						<tr>
							<td class="text-center align-middle">{{ $al->year }}</td>
							<td class="text-center align-middle">{{ $al->annual_leave }}</td>
							<td class="text-center align-middle">{{ $al->annual_leave_adjustment }}</td>
							<td class="text-center align-middle">{{ $al->annual_leave_utilize }}</td>
							<td class="text-center align-middle">{{ $al->annual_leave_balance }}</td>
							<td class="text-center align-middle">
								<a href="{{ route('annualleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			@endif
			@if($staff->hasmanyleavemc()?->get()->count())
			<div class="col-4" style="background-color: #f2f2f2;">MC Leave :</div>
			<div class="col-8">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle" width="35px;">Year</th>
							<th class="text-center align-middle" width="98px;">MC Entitlement</th>
							<th class="text-center align-middle" width="95px;">MC Adjustment</th>
							<th class="text-center align-middle" width="68px;">MC Utilize</th>
							<th class="text-center align-middle">MC Balance</th>
							<th class="text-center align-middle" width="40px;">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					@foreach($staff->hasmanyleavemc()->orderBy('year', 'DESC')->get() as $al)
						<tr>
							<td class="text-center align-middle">{{ $al->year }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave_adjustment }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave_utilize }}</td>
							<td class="text-center align-middle">{{ $al->mc_leave_balance }}</td>
							<td class="text-center align-middle">
								<a href="{{ route('mcleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			@endif
			@if($staff->gender_id == 2)
				@if($staff->hasmanyleavematernity()?->get()->count())
				<div class="col-4">Maternity Leave :</div>
				<div class="col-8">
					<table class="table table-sm table-hover" style="font-size:12px;">
						<thead>
							<tr>
								<th class="text-center align-middle" width="35px;">Year</th>
								<th class="text-center align-middle" width="98px;">Maternity Entitlement</th>
								<th class="text-center align-middle" width="95px;">Maternity Adjustment</th>
								<th class="text-center align-middle" width="68px;">Maternity Utilize</th>
								<th class="text-center align-middle">Maternity Balance</th>
								<th class="text-center align-middle" width="40px;">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
						@foreach($staff->hasmanyleavematernity()->orderBy('year', 'DESC')->get() as $al)
							<tr>
								<td class="text-center align-middle">{{ $al->year }}</td>
								<td class="text-center align-middle">{{ $al->maternity_leave }}</td>
								<td class="text-center align-middle">{{ $al->maternity_leave_adjustment }}</td>
								<td class="text-center align-middle">{{ $al->maternity_leave_utilize }}</td>
								<td class="text-center align-middle">{{ $al->maternity_leave_balance }}</td>
								<td class="text-center align-middle">
									<a href="{{ route('maternityleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				@endif
			@endif

		</div>
	</div>

	<p>&nbsp;</p>
	<div class="row justify-content-center">
		<div class="col-sm-12">
			<canvas id="myChart" width="200" height="75"></canvas>
		</div>
	</div>

	<p>&nbsp;</p>
	<div class="row justify-content-center">
		<div id="calendar"></div>
	</div>

	<p>&nbsp;</p>
	<div class="container row align-items-start justify-content-center">
		<h4 class="align-items-center">Attendance</h4>
		<table id="attendance" class="table table-hover table-sm align-middle" style="font-size:13px">
			<thead>
				<tr>
					<th class="text-center" width="60px">Date</th>
					<th class="text-center" width="60px">Day Type</th>
					<th class="text-center" width="45px">In</th>
					<th class="text-center" width="45px">Break</th>
					<th class="text-center" width="45px">Resume</th>
					<th class="text-center" width="45px">Out</th>
					<th class="text-center" width="55px">W/Hour</th>
					<th class="text-center" width="60px">Overtime</th>
					<th class="text-center" width="75px">Leave Form</th>
					<th class="text-center" width="70px">Leave Type</th>
					<th class="text-center">Remark</th>
					<th class="text-center" width="120px">Outstation</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($attendance as $attend)

				<?php
				$in = NULL;
				$break = NULL;
				$resume = NULL;
				$out = NULL;
				$work_hour = NULL;
				$leave_id = NULL;
				$leave_form = NULL;
				$leave_type = NULL;

				$date_name = Carbon::parse($attend->attend_date)->format('l');

				if ($wh_group == '0' && $date_name == 'Friday') {
					$company_hour = \App\Models\HumanResources\OptWorkingHour::where('option_working_hours.group', '=', $wh_group)
						->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
						->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
						->where('option_working_hours.category', '=', 3)
						->select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')
						->first();
				} elseif ($wh_group == '0') {
					$company_hour = \App\Models\HumanResources\OptWorkingHour::where('option_working_hours.group', '=', $wh_group)
						->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
						->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
						->where('option_working_hours.category', '!=', 3)
						->select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')
						->first();
				} else {
					$company_hour = \App\Models\HumanResources\OptWorkingHour::where('option_working_hours.group', '=', $wh_group)
						->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
						->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
						->where('option_working_hours.category', '=', 8)
						->select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')
						->first();
				}

				$daytype = $attend->belongstodaytype()->first();
				$outstation = $attend->belongstooutstation?->belongstocustomer?->customer;
				$overtime = $attend->belongstoovertime?->belongstoovertimerange?->total_time;

				if ($attend->in != NULL && $attend->in != '00:00:00') {
					$in = Carbon::parse($attend->in)->format('h:i a');
				}

				if ($attend->in > $company_hour->time_start_am) {
					$color_in = "color:red";
				} else {
					$color_in = NULL;
				}

				if ($attend->break != NULL && $attend->break != '00:00:00') {
					$break = Carbon::parse($attend->break)->format('h:i a');
				}

				if ($attend->break < $company_hour->time_end_am) {
					$color_break = "color:red";
				} else {
					$color_break = NULL;
				}

				if ($attend->resume != NULL && $attend->resume != '00:00:00') {
					$resume = Carbon::parse($attend->resume)->format('h:i a');
				}

				if ($attend->resume > $company_hour->time_start_pm) {
					$color_resume = "color:red";
				} else {
					$color_resume = NULL;
				}

				if ($attend->out != NULL && $attend->out != '00:00:00') {
					$out = Carbon::parse($attend->out)->format('h:i a');
				}

				if ($attend->out < $company_hour->time_end_pm) {
					$color_out = "color:red";
				} else {
					$color_out = NULL;
				}

				if ($attend->time_work_hour != NULL && $attend->time_work_hour != '00:00:00') {
					$work_hour = Carbon::parse($attend->time_work_hour)->format('H:i');
				}

				if ($attend->leave_id != NULL && $attend->leave_id != '') {
					$leave_temp1 = $attend->belongstoleave()->first();
					$leave_temp2 = $attend->belongstoleave->belongstooptleavetype()->first();

					$leave_id = $leave_temp1->id;

					$leave_form = "HR9-" . str_pad($leave_temp1->leave_no, 5, '0', STR_PAD_LEFT) . "/" . $leave_temp1->leave_year;

					$leave_type = $leave_temp2->leave_type_code;
				}
				?>

				<tr>
					<td class="text-center">
						{{ $attend->attend_date }}
					</td>
					<td class="text-center">
						{{ $daytype->daytype }}
					</td>
					<td class="text-center">
						<span style="{{ $color_in }}">{{ $in }}</span>
					</td>
					<td class="text-center">
					<span style="{{ $color_break }}">{{ $break }}</span>
					</td>
					<td class="text-center">
					<span style="{{ $color_resume }}">{{ $resume }}</span>
					</td>
					<td class="text-center">
					<span style="{{ $color_out }}">{{ $out }}</span>
					</td>
					<td class="text-center">
						{{ $work_hour }}
					</td>
					<td class="text-center" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $overtime }}">
						{{ $overtime }}
					</td>
					<td class="text-center">
						@if ($leave_id != NULL)
						<a href="{{ route('leave.show', $leave_id) }}" target="_blank">
							{{ $leave_form }}
						</a>
						@endif
					</td>
					<td class="text-center">
						{{ $leave_type }}
					</td>
					<td class="text-truncate" style="max-width: 1px;" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $attend->attend_remark }}">
						{{ $attend->attend_remark }}
					</td>
					<td class="text-truncate" style="max-width: 120px;" data-bs-toggle="tooltip" data-bs-html="true" title="{{ $outstation }}">
						{{ $outstation }}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<div class="row justify-content-center">
		<div class="col-sm-12 row gy-1 gx-1 align-items-start">
			<h4 class="align-items-center">Leave</h4>
			@if(\App\Models\HumanResources\HRLeave::where('staff_id', $staff->id)->get()->count())
			<table id="leave" class="table table-sm table-hover" style="font-size:12px;">
				<thead>
					<tr>
						<th>No</th>
						<th>Type</th>
						<th>From</th>
						<th>To</th>
						<th>Duration</th>
						<th>Reason</th>
						<th>Status</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					@foreach(\App\Models\HumanResources\HRLeave::where('staff_id', $staff->id)->orderBy('date_time_start', 'DESC')->orderBy('leave_type_id', 'ASC')->orderBy('leave_status_id', 'DESC')->get() as $ls)
<?php
$dts = \Carbon\Carbon::parse($ls->date_time_start)->format('Y');
$dte = \Carbon\Carbon::parse($ls->date_time_end)->format('j M Y g:i a');
// only available if only now is before date_time_start and active is 1
$dtsl = \Carbon\Carbon::parse( $ls->date_time_start );
$dt = \Carbon\Carbon::now()->lte( $dtsl );

if ( ($ls->leave_type_id == 9) || ($ls->leave_type_id != 9 && $ls->half_type_id == 2) || ($ls->leave_type_id != 9 && $ls->half_type_id == 1) ) {
	$dts = \Carbon\Carbon::parse($ls->date_time_start)->format('j M Y g:i a');
	$dte = \Carbon\Carbon::parse($ls->date_time_end)->format('j M Y g:i a');

	if ($ls->leave_type_id != 9) {
		if ($ls->half_type_id == 2) {
			$dper = $ls->period_day.' Day';
		} elseif($ls->half_type_id == 1) {
			$dper = $ls->period_day.' Day';
		}
	}elseif ($ls->leave_type_id == 9) {
		$i = \Carbon\Carbon::parse($ls->period_time);
		$dper = $i->hour.' hour, '.$i->minute.' minutes';
	}

} else {
	$dts = \Carbon\Carbon::parse($ls->date_time_start)->format('j M Y ');
	$dte = \Carbon\Carbon::parse($ls->date_time_end)->format('j M Y ');
	$dper = $ls->period_day.' day/s';
}
?>
					<tr>
						<td>HR9-{{ str_pad( $ls->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $ls->leave_year }}</td>
						<td>{{ $ls->belongstooptleavetype?->leave_type_code }}</td>
						<td>{{ $dts }}</td>
						<td>{{ $dte }}</td>
						<td>{{ $dper }}</td>
						<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $ls->reason }}">{{ Str::limit($ls->reason, 10, '>') }}</td>
						<td>
							@if(is_null($ls->leave_status_id))
								Pending
							@else
								{{ $ls->belongstooptleavestatus?->status }}
							@endif
						</td>
						<td>
							<a href="{{ route('hrleave.show', $ls->id) }}" class="btn btn-sm btn-outline-secondary">
								<i class="fa-regular fa-eye"></i>
							</a>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
			@else
			<p>No Leave Yet</p>
			@endif
		</div>
	</div>

	<p>&nbsp;</p>
	<div class="row justify-content-center">
		<div class="col-sm-12 row gy-1 gx-1 align-items-start">
			<h4 class="align-items-center">Replacement Leave</h4>
				@if($staff->hasmanyleavereplacement()?->get()->count())
					<table class="table table-sm table-hover" style="font-size:12px;" id="replacementleave">
						<thead>
							<tr>
								<th>From</th>
								<th>To</th>
								<th>Location</th>
								<th>Reason</th>
								<th>Total Day/s</th>
								<th>Leave Utilize</th>
								<th>Leave Balance</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody>
						@foreach($staff->hasmanyleavereplacement()->orderBy('date_start', 'DESC')->get() as $al)
							<tr>
								<td>{{ \Carbon\Carbon::parse($al->date_start)->format('j M Y') }}</td>
								<td>{{ \Carbon\Carbon::parse($al->date_end)->format('j M Y') }}</td>
								<td>{{ $al->belongstocustomer?->customer }}</td>
								<td>{{ $al->reason }}</td>
								<td>{{ $al->leave_total }}</td>
								<td>{{ $al->leave_utilize }}</td>
								<td>{{ $al->leave_balance }}</td>
								<td>
									<a href="{{ route('rleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary">
										<i class="fa-regular fa-pen-to-square"></i>
									</a>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				@else
					<p>No Leave Yet</p>
				@endif
			</div>
		</div>
	<p>&nbsp;</p>
	<div class="row justify-content-center">
		<div class="col-sm-12 row gy-1 gx-1 align-items-start">
			<h4 class="align-items-center">Disciplinary</h4>
			@if($staff->hasmanyhrdisciplinary()?->get()?->count())
				<table class="table table-sm table-hover" style="font-size:12px;" id="disc">
					<thead>
						<tr>
							<th>Discipline Action</th>
							<th>Violation</th>
							<th>Reason</th>
							<th>Date</th>
							<th>Softcopy</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					@foreach($staff->hasmanyhrdisciplinary()->orderBy('date', 'DESC')->get() as $al)
						<tr>
							<td>{{ $al->belongstooptdisciplinaryaction->disciplinary_action }}</td>
							<td>{{ $al->belongstooptviolation->violation }}</td>
							<td data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $al->reason }}">
								{{ Str::limit($al->reason, 10, '>') }}
							</td>
							<td>{{ \Carbon\Carbon::parse($al->date)->format('j M Y') }}</td>
							<td>
								@if($al->softcopy)
								<a href="{{ asset('storage/disciplinary/' . $al->softcopy) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
									<i class="bi bi-file-text" style="font-size: 15px;"></i>
								</a>
								@endif
							</td>
							<td>
								<a href="{{ route('discipline.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
								&nbsp;
								<button type="button" class="btn btn-sm btn-outline-secondary delete_discipline" data-id="{{ $al->id }}" data-softcopy="{{ $al->softcopy }}" data-table="discipline">
									<i class="fa-regular fa-trash-can"></i>
								</button>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@else
				<p>No Disciplinary Action</p>
			@endif
		</div>
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$(document).on('click', '.deactivate', function(e){
	var staffId = $(this).data('id');
	DeactivateStaff(staffId);
	e.preventDefault();
});

function DeactivateStaff(staffId){
	swal.fire({
		title: 'Are you sure?',
		text: "Please take note, this action will deactivate {{ $staff->name }}.",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, deactivate',
		showLoaderOnConfirm: true,

		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: 'PATCH',
					url: '{{ url('deactivatestaff') }}' + '/' + staffId,
					data: {
							_token : $('meta[name=csrf-token]').attr('content'),
							id: staffId,
					},
					dataType: 'json'
				})
				.done(function(response){
					swal.fire('Deleted!', response.message, response.status)
					.then(function(){
						window.location.reload(true);
					});
					//$('#disable_user_' + staffId).parent().parent().remove();
					window.location.replace('{{ route('staff.index') }}');
				})
				.fail(function(){
					swal.fire('Oops...', 'Something went wrong with system! Please try again later', 'error');
				})
			});
		},
		allowOutsideClick: false
	})
	.then((result) => {
		if (result.dismiss === swal.DismissReason.cancel) {
			swal.fire('Cancelled', 'Your {{ $staff->name }} is safe from deactivate', 'info')
		}
	});
}


/////////////////////////////////////////////////////////////////////////////////////////
// tooltip on reason
$(document).ready(function(){
	$('[data-bs-toggle="tooltip"]').tooltip();
});


/////////////////////////////////////////////////////////////////////////////////////////
// datatables
$.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'h:mm a' );
$('#attendance').DataTable({
	"paging": true,
	"lengthMenu": [ [30, 60, 100, -1], [30, 60, 100, "All"] ],
	"columnDefs": [
		{ type: 'date', 'targets': [0] },
		{ type: 'time', 'targets': [2] },
		{ type: 'time', 'targets': [3] },
		{ type: 'time', 'targets': [4] },
		{ type: 'time', 'targets': [5] },
		{ type: 'time', 'targets': [6] },
	],
	"order": [[ 0, 'asc' ]], // sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});
});


/////////////////////////////////////////////////////////////////////////////////////////
// datatables
// $.fn.dataTable.moment( 'D MMM YYYY' );
$.fn.dataTable.moment( 'D MMM YYYY h:mm a' );
$('#leave').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [ { type: 'date', 'targets': [2,3] } ],
	"order": [[2, "desc" ]],	// sorting the 6th column descending
	responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});
});

$('#replacementleave').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [ { type: 'date', 'targets': [0,1] } ],
	"order": [[0, "desc" ]],	// sorting the 6th column descending
	// responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

$('#disc').DataTable({
	"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
	"columnDefs": [ { type: 'date', 'targets': [3] } ],
	"order": [[3, "desc" ]],	// sorting the 6th column descending
	// responsive: true
})
.on( 'length.dt page.dt order.dt search.dt', function ( e, settings, len ) {
	$(document).ready(function(){
		$('[data-bs-toggle="tooltip"]').tooltip();
	});}
);

// DELETE
$(document).on('click', '.delete_discipline', function(e){
	var ackID = $(this).data('id');
	var ackSoftcopy = $(this).data('softcopy');
	var ackTable = $(this).data('table');
	SwalDelete(ackID, ackSoftcopy, ackTable);
	e.preventDefault();
});

function SwalDelete(ackID, ackSoftcopy, ackTable){
	swal.fire({
		title: 'Delete Discipline',
		text: 'Are you sure to delete this discipline?',
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
					url: '{{ url('discipline') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
						id: ackID,
						softcopy: ackSoftcopy,
						table: ackTable,
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
	});
}

@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
// fullcalendar cant use jquery
// import { Calendar } from '@fullcalendar/core'
// import multiMonthPlugin from '@fullcalendar/multimonth'

document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');

	var calendar = new FullCalendar.Calendar(calendarEl, {
		aspectRatio: 1.0,
		height: 500,
		// plugins: [multiMonthPlugin],
		// initialView: 'multiMonthYear',
		// multiMonthMaxColumns: 1,					// force a single column
		initialView: 'dayGridMonth',
		weekNumbers: true,
		themeSystem: 'bootstrap',
		events: {
			url: '{{ route('staffattendance') }}',
			method: 'POST',
			extraParams: {
				_token: '{!! csrf_token() !!}',
				staff_id: '{{ $staff->id }}',
			},
		},
		// failure: function() {
		// 	alert('There was an error while fetching leaves!');
		// },
		eventDidMount: function(info) {
				var tooltip = new Tooltip(info.el, {
					title: info.event.extendedProps.description,
					placement: 'top',
					trigger: 'hover',
					container: 'body'
				});
		},
		eventTimeFormat: { // like '14:30:00'
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hour12: true
		}
	});
	calendar.render();
});


/////////////////////////////////////////////////////////////////////////////////////////
// chartjs also dont use jquery

// const data = [
// 					{ month: 'January', percentage: 90.59, workdays: 31, leaves: 1, absents: 1, working_days: 25 },
// 					{ month: 'February', percentage: 93.23, workdays: 28, leaves: 1, absents: 1, working_days: 25 },
// 					{ month: 'March', percentage: 91.5, workdays: 31, leaves: 1, absents: 1, working_days: 25 },
// 					{ month: 'April', percentage: 93.45, workdays: 30, leaves: 1, absents: 1, working_days: 25 },
// 					{ month: 'May', percentage: 81.23, workdays: 31, leaves: 1, absents: 1, working_days: 25 },
// 					{ month: 'June', percentage: 79.23, workdays: 30, leaves: 1, absents: 1, working_days: 25 },
// 					{ month: 'July', percentage: 95.59, workdays: 31, leaves: 1, absents: 1, working_days: 25 },
// 			];

var xmlhttp = new XMLHttpRequest();
// xmlhttp.open(method, URL, [async, user, password])
xmlhttp.open("POST", '{!! route('staffpercentage', ['id' => $staff->id, '_token' => csrf_token()]) !!}', true);
// xmlhttp.responseType = 'json';
// xmlhttp.onreadystatechange = myfunction;
xmlhttp.send();
xmlhttp.onload = function() {
// alert(`Loaded: ${data.status} ${data.response}`);
// return data.status;
	const data = JSON.parse(xmlhttp.responseText);
//	console.log(data);

	new Chart(document.getElementById('myChart'), {
		type: 'line',
		data: {
			labels: data.map(row => row.month),
			datasets: [
						{
							type: 'line',
							label: 'Attendance Percentage By Month(%)',
							data: data.map(row => row.percentage),
							tension: 0.3,
						},
						{
							type: 'bar',
							label: 'Leaves By Month',
							data: data.map(row => row.leaves)
						},
						{
							type: 'bar',
							label: 'Absents By Month',
							data: data.map(row => row.absents)
						},
						{
							type: 'bar',
							label: 'Working Days By Month (Person Available)',
							data: data.map(row => row.working_days)
						},
						{
							type: 'bar',
							label: 'Work Days By Month',
							data: data.map(row => row.workdays)
						},
			]
		},
		options: {
			responsive: true,
			scales: {
				y: {
					beginAtZero: true
				}
			},
			interaction: {
				intersect: false,
				mode: 'index',
			},
		},
		plugins: {
			legend: {
				position: 'top',
			},
			title: {
				display: true,
				text: 'Attendance Statistic'
			},
		},
	});
};

@endsection
