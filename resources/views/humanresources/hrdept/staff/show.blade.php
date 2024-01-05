@extends('layouts.app')

@section('content')
<script>
// Check if session storage is supported by the browser
if (typeof(Storage) !== 'undefined') {
	// Store the current scroll position in session storage on page unload
	window.addEventListener('beforeunload', function() {
		sessionStorage.setItem('scrollPosition', window.scrollY);
	});

	// Restore the scroll position on page load
	window.addEventListener('load', function() {
		var scrollPosition = sessionStorage.getItem('scrollPosition');
		if (scrollPosition !== null) {
			window.scrollTo(0, scrollPosition);
			sessionStorage.removeItem('scrollPosition');
		}
	});
}
</script>

<?php
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptLeaveType;
use Illuminate\Database\Eloquent\Builder;
use \Carbon\Carbon;

// entitlement
$annl = $staff->hasmanyleaveannual()?->where('year', now()->format('Y'))->first();
$mcel = $staff->hasmanyleavemc()?->where('year', now()->format('Y'))->first();
$matl = $staff->hasmanyleavematernity()?->where('year', now()->format('Y'))->first();
$replt = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')->where(function(Builder $query){$query->whereDate('date_start', '>=', now()->startOfYear())->whereDate('date_end', '<=', now()->endOfYear());})->get();
$replb = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')->where(function(Builder $query){$query->whereDate('date_start', '>=', now()->startOfYear())->whereDate('date_end', '<=', now()->endOfYear());})->get();
$upal = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
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
$mcupl = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
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
$mcupl = $staff->hasmanyleave()?->get();
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
	<div class="d-flex flex-column align-items-center text-center">
		<img class="rounded-5 m-3" src="{{ asset('storage/user_profile/' . $staff->image) }}" style="width: 200px;">
		<span class="font-weight-bold">{{ $staff->name }}</span>
		<span class="font-weight-bold">{{ $staff->hasmanylogin()->where('active', 1)->first()?->username }}</span>
	</div>
	<div>&nbsp;</div>
	<div class="col-sm-6 row">
		<dl class="row">
			<dt class="col-sm-5">Name :</dt>
			<dd class="col-sm-7">{{ $staff->name }}</dd>
			<dt class="col-sm-5">Identity Card/Passport :</dt>
			<dd class="col-sm-7">{{ $staff->ic }}</dd>
			<dt class="col-sm-5">Religion :</dt>
			<dd class="col-sm-7">{{ $staff->belongstoreligion?->religion }}</dd>
			<dt class="col-sm-5">Gender :</dt>
			<dd class="col-sm-7">{{ $staff->belongstogender?->gender }}</dd>
			<dt class="col-sm-5">Race :</dt>
			<dd class="col-sm-7">{{ $staff->belongstorace?->race }}</dd>
			<dt class="col-sm-5">Nationality :</dt>
			<dd class="col-sm-7">{{ $staff->belongstonationality?->country }}</dd>
			<dt class="col-sm-5">Marital Status :</dt>
			<dd class="col-sm-7">{{ $staff->belongstomaritalstatus?->marital_status }}</dd>
			<dt class="col-sm-5">Email :</dt>
			<dd class="col-sm-7">{{ $staff->email }}</dd>
			<dt class="col-sm-5">Address :</dt>
			<dd class="col-sm-7">{{ $staff->address }}</dd>
			<dt class="col-sm-5">Place of Birth :</dt>
			<dd class="col-sm-7">{{ $staff->place_of_birth }}</dd>
			<dt class="col-sm-5">Mobile :</dt>
			<dd class="col-sm-7">{{ $staff->mobile }}</dd>
			<dt class="col-sm-5">Phone :</dt>
			<dd class="col-sm-7">{{ $staff->phone }}</dd>
			<dt class="col-sm-5">Date of Birth :</dt>
			<dd class="col-sm-7">{{ \Carbon\Carbon::parse($staff->dob)->format('j M Y') }}</dd>
			<dt class="col-sm-5">CIMB Account :</dt>
			<dd class="col-sm-7">{{ $staff->cimb_account }}</dd>
			<dt class="col-sm-5">EPF Account :</dt>
			<dd class="col-sm-7">{{ $staff->epf_account }}</dd>
			<dt class="col-sm-5">Income Tax No :</dt>
			<dd class="col-sm-7">{{ $staff->income_tax_no }}</dd>
			<dt class="col-sm-5">SOCSO No :</dt>
			<dd class="col-sm-7">{{ $staff->socso_no }}</dd>
			<dt class="col-sm-5">Weight :</dt>
			<dd class="col-sm-7">{{ $staff->weight }} kg</dd>
			<dt class="col-sm-5">Height :</dt>
			<dd class="col-sm-7">{{ $staff->height }} cm</dd>
			<dt class="col-sm-5">Date Join :</dt>
			<dd class="col-sm-7">{{ \Carbon\Carbon::parse($staff->join)->format('j M Y') }}</dd>
			<dt class="col-sm-5">Date Confirmed :</dt>
			<dd class="col-sm-7">{{ \Carbon\Carbon::parse($staff->confirmed)->format('j M Y') }}</dd>
			<dt class="col-sm-5">Spouse :</dt>
			<dd class="col-sm-7">
				<div class="table-responsive">
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
			</dd>
			<dt class="col-sm-5">Children :</dt>
			<dd class="col-sm-7">
				<div class="table-responsive">
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
			</dd>
			<dt class="col-sm-5">Emergency Contact :</dt>
			<dd class="col-sm-7">
				<div class="table-responsive">
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
			</dd>
		</dl>
	</div>

	<div class="col-sm-6 row">
		<dl class="row">
			<dt class="col-sm-4">System Administrator :</dt>
			<dd class="col-sm-8">{{ $staff->belongstoauthorised?->authorise }}</dd>
			<dt class="col-sm-4">Staff Status :</dt>
			<dd class="col-sm-8">{{ $staff->belongstostatus?->status }}</dd>
			<dt class="col-sm-4">Category :</dt>
			<dd class="col-sm-8">{{ $staff->belongstomanydepartment()?->wherePivot('main', 1)->first()->belongstocategory?->category }}</dd>
			<dt class="col-sm-4">Branch :</dt>
			<dd class="col-sm-8">{{ $staff->belongstomanydepartment()?->wherePivot('main', 1)->first()->belongstobranch?->location }}</dd>
			<dt class="col-sm-4">Department :</dt>
			<dd class="col-sm-8">{{ $staff->belongstomanydepartment()?->wherePivot('main', 1)->first()->department }}</dd>
			<dt class="col-sm-4">Leave Approval Flow :</dt>
			<dd class="col-sm-8">{{ $staff->belongstoleaveapprovalflow?->description }}</dd>
			<dt class="col-sm-4">RestDay Group :</dt>
			<dd class="col-sm-8">{{ $staff->belongstorestdaygroup?->group }}</dd>
			<dt class="col-sm-4">Cross Backup To :</dt>
			<?php
			$cb = $staff->crossbackupto()->get();
			?>
			<dd class="col-sm-8">
				@if($cb->count())
				<ul>
					@foreach($cb as $r)
					<li>{{ $r->name }}</li>
					@endforeach
				</ul>
				@endif
			</dd>
			<dt class="col-sm-4">Cross Backup For :</dt>
			<?php
			$cbf = $staff->crossbackupfrom()->get();
			?>
			<dd class="col-sm-8">
				@if($cbf->count())
				<ul>
					@foreach($cbf as $rf)
					<li>{{ $rf->name }}</li>
					@endforeach
				</ul>
				@endif
			</dd>
			@if($staff->hasmanyleaveannual()?->get()->count())
			<dt class="col-sm-4">Annual Leave :</dt>
			<dd class="col-sm-8 table-responsive">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">Year</th>
							<th class="text-center align-middle">AL Entitlement</th>
							<th class="text-center align-middle">AL Adjustment</th>
							<th class="text-center align-middle">AL Utilize</th>
							<th class="text-center align-middle">AL Balance</th>
							<th class="text-center align-middle">&nbsp;</th>
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
			</dd>
			@endif
			@if($staff->hasmanyleavemc()?->get()->count())
			<dt class="col-sm-4">MC Leave :</dt>
			<dd class="col-sm-8 table-responsive">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">Year</th>
							<th class="text-center align-middle">MC Entitlement</th>
							<th class="text-center align-middle">MC Adjustment</th>
							<th class="text-center align-middle">MC Utilize</th>
							<th class="text-center align-middle">MC Balance</th>
							<th class="text-center align-middle">&nbsp;</th>
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
			</dd>
			@endif
			@if($staff->gender_id == 2)
			@if($staff->hasmanyleavematernity()?->get()->count())
			<dt class="col-sm-4">Maternity Leave :</dt>
			<dd class="col-sm-8 table-responsive">
				<table class="table table-sm table-hover" style="font-size:12px;">
					<thead>
						<tr>
							<th class="text-center align-middle">Year</th>
							<th class="text-center align-middle">Maternity Entitlement</th>
							<th class="text-center align-middle">Maternity Adjustment</th>
							<th class="text-center align-middle">Maternity Utilize</th>
							<th class="text-center align-middle">Maternity Balance</th>
							<th class="text-center align-middle">&nbsp;</th>
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
			</dd>
			@endif
			@endif
		</dl>
	</div>

	<div class="col-sm-12 table-responsive">
		<h4>Entitlements</h4>
		<table class="table table-sm table-hover table-bordered" style="font-size: 12px;">
			<thead>
				<tr>
					<th class="text-center" rowspan="3">Year</th>
					<th class="text-center" colspan="2">Annual Leave (AL)</th>
					<th class="text-center" colspan="2">Medical Certificate Leave (MC)</th>
					<th class="text-center" colspan="2">Maternity Leave (ML)</th>
					<th class="text-center" colspan="2">Replacement Leave (NRL)</th>
					<th class="text-center">Unpaid Leave (UPL)</th>
					<th class="text-center">Medical Certificate Unpaid Leave (MC-UPL)</th>
				</tr>
				<tr>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Balance (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Total (days)</th>
					<th class="text-center">Total (days)</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-center">{{ now()->format('Y') }}</td>
					<td class="text-center">{{ $annl?->annual_leave_balance }}</td>
					<td class="text-center">{{ $annl?->annual_leave + $annl?->annual_leave_adjustment }}</td>
					<td class="text-center">{{ $mcel?->mc_leave_balance }}</td>
					<td class="text-center">{{ $mcel?->mc_leave + $mcel?->mc_leave_adjustment }}</td>
					<td class="text-center">{{ $matl?->maternity_leave_balance }}</td>
					<td class="text-center">{{ $matl?->maternity_leave + $matl?->maternity_leave_adjustment }}</td>
					<td class="text-center">{{ $replb?->first()?->total }}</td>
					<td class="text-center">{{ $replt?->first()?->total }}</td>
					<td class="text-center">{{ $upal?->first()?->total }}</td>
					<td class="text-center">{{ $mcupl?->first()?->total }}</td>
				</tr>
			</tbody>
		</table>


	</div>

	<p>&nbsp;</p>
	<div class="col-sm-12">
		<canvas id="myChart"></canvas>
	</div>

	<p>&nbsp;</p>
	<div id="calendar" class="col-sm-12"></div>

	<?php
	use App\Models\Staff;
	use App\Models\HumanResources\HRAttendance;

	$group_year = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
		->select(DB::raw('YEAR(hr_attendances.attend_date) AS year'))
		->where('hr_attendances.staff_id', $staff->id)
		->groupBy('year')
		->orderBy('year', 'desc')
		->pluck('year', 'year')
		->toArray();

	$group_month = ['01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05', '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10', '11'=>'11', '12'=>'12'];
	?>

	<p>&nbsp;</p>
	<h4 class="align-items-center">Attendance</h4>
	<div class="table-responsive">

		{{ Form::open(['route' => ['staff.show', $staff->id], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

		<table width="100%">
			<tr>
				<td></td>
				<td width="100px">
					{{ Form::select('year', $group_year, @$year, ['class' => 'form-control form-control-sm form-select', 'id' => 'year', 'placeholder' => '', 'autocomplete' => 'off']) }}
				</td>
				<td width="5px"></td>
				<td width="80px">
					{{ Form::select('month', $group_month, @$month, ['class' => 'form-control form-control-sm form-select', 'id' => 'month', 'placeholder' => '', 'autocomplete' => 'off']) }}
				</td>
				<td width="5px"></td>
				<td width="70px">
					{!! Form::submit('SEARCH', ['class' => 'form-control form-control-sm btn btn-sm btn-outline-secondary']) !!}
				</td>
			</tr>
		</table>

		{!! Form::close() !!}

		<table id="attendance" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th class="text-center">Date</th>
					<th class="text-center">Day Type</th>
					<th class="text-center">In</th>
					<th class="text-center">Break</th>
					<th class="text-center">Resume</th>
					<th class="text-center">Out</th>
					<th class="text-center">W/Hour</th>
					<th class="text-center">Overtime</th>
					<th class="text-center">Leave Form</th>
					<th class="text-center">Leave Type</th>
					<th class="text-center">Remark</th>
					<th class="text-center">Outstation</th>
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
						{{ ($attend->attend_date)?\Carbon\Carbon::parse($attend->attend_date)->format('j M Y'):null }}
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
					<td {!! ($attend->attend_remark)?'class="text-truncate" data-bs-toggle="tooltip" data-bs-html="true" title="'.$attend->attend_remark.'"':null !!}>
						{{ Str::limit($attend->attend_remark, 7, ' >>') }}
					</td>
					<td {!! ($outstation)?'class="text-truncate" data-bs-toggle="tooltip" data-bs-html="true" title="'.$outstation.'"':null !!}>
						{{ Str::limit($outstation, 7, ' >>') }}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<p>&nbsp;</p>
	<h4 class="align-items-center">Leave</h4>
	<div class="table-responsive">
		@if(\App\Models\HumanResources\HRLeave::where('staff_id', $staff->id)->get()->count())
		<table id="leave" class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th>No</th>
					<th>Type</th>
					<th>Applied Date</th>
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
					<td>{{ Carbon::parse($ls->created_at)->format('j M Y g:i a') }}</td>
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

	<p>&nbsp;</p>
	<h4>Annual Leave Entitlement</h4>
	@if($staff->hasmanyleaveannual()?->get()->count())
	<div class="table-responsive">
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">AL Entitlement</th>
					<th class="text-center align-middle">AL Adjustment</th>
					<th class="text-center align-middle">AL Utilize</th>
					<th class="text-center align-middle">AL Balance</th>
					<th class="text-center align-middle">Leave</th>
					<th class="text-center align-middle">&nbsp;</th>
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
					<td class="table-responsive">
						<?php
						$leaves = HRLeave::where(function(Builder $query) {
												$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
											})
											->where('staff_id', $staff->id)
											->whereYear('date_time_start', $al->year)
											->whereIn('leave_type_id', [1, 5])
											->get();
						?>
						@if($leaves->count())
						<table class="table table-hover table-sm">
							<thead>
								<tr>
									<th>Leave ID</th>
									<th>Duration</th>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								@foreach($leaves as $key => $leave)
									<tr>
										<td>
											<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
										</td>
										<td>
											{{ $leave->period_day }} day/s
											<?php $total += $leave->period_day; ?>
										</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr>
									<td>Total</td>
									<td>{{ $total }} day/s</td>
								</tr>
							</tfoot>
						</table>
						@endif
					</td>
					<td class="text-center align-middle">
						<a href="{{ route('annualleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endif

	<p>&nbsp;</p>
	<h4>Medical Certificate Leave</h4>
	<div class="table-responsive">
	@if($staff->hasmanyleavemc()?->get()->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">MC Entitlement</th>
					<th class="text-center align-middle">MC Adjustment</th>
					<th class="text-center align-middle">MC Utilize</th>
					<th class="text-center align-middle">MC Balance</th>
					<th class="text-center align-middle">Leave</th>
					<th class="text-center align-middle">&nbsp;</th>
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
						<?php
						$leaves = HRLeave::where(function(Builder $query) {
												$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
											})
											->where('staff_id', $staff->id)
											->whereYear('date_time_start', $al->year)
											->where('leave_type_id', 2)
											->get();
						?>
						@if($leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									<?php $total = 0; ?>
									@foreach($leaves as $key => $leave)
										<tr>
											<td>
												<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
												<?php $total += $leave->period_day; ?>
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>Total</td>
										<td>{{ $total }} day/s</td>
									</tr>
								</tfoot>
							</table>
						@endif
					</td>
					<td class="text-center align-middle">
						<a href="{{ route('mcleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@endif
	</div>

	@if($staff->gender_id == 2)
	<p>&nbsp;</p>
	<h4>Maternity Leave</h4>
	<div class="table-responsive">
		@if($staff->hasmanyleavematernity()?->get()->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">Year</th>
					<th class="text-center align-middle">Maternity Entitlement</th>
					<th class="text-center align-middle">Maternity Adjustment</th>
					<th class="text-center align-middle">Maternity Utilize</th>
					<th class="text-center align-middle">Maternity Balance</th>
					<th class="text-center align-middle">Leave</th>
					<th class="text-center align-middle">&nbsp;</th>
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
						<?php
						$leaves = HRLeave::where(function(Builder $query) {
										$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
									})
									->where('staff_id', $staff->id)
									->where('leave_type_id', 7)
									->get();
						?>
						@if($leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									<?php $total = 0; ?>
									@foreach($leaves as $key => $leave)
										<tr>
											<td>
												<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
												<?php $total += $leave->period_day; ?>
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>Total</td>
										<td>{{ $total }} day/s</td>
									</tr>
								</tfoot>
							</table>
						@endif
					</td>
					<td class="text-center align-middle">
						<a href="{{ route('maternityleave.edit', $al->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
	@endif

	<p>&nbsp;</p>
	<h4>Unpaid Leave</h4>
	<div class="table-responsive">
	<?php
	$leavesupls = HRLeave::where(function(Builder $query) {
							$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
						})
						->where('staff_id', $staff->id)
						->whereIn('leave_type_id', [3, 6, 12])
						->get();
	$dur = 0;
	?>
	@if($leavesupls->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">ID</th>
					<th class="text-center align-middle">Leave Type</th>
					<th class="text-center align-middle">From</th>
					<th class="text-center align-middle">To</th>
					<th class="text-center align-middle">Duration</th>
				</tr>
			</thead>
			<tbody>
				@foreach($leavesupls as $leavesupl)
				<tr>
					<td class="text-center align-middle">
						<a href="{{ route('hrleave.show', $leavesupl->id) }}" target="_blank">HR9-{{ str_pad( $leavesupl->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leavesupl->leave_year }}</a>
					</td>
					<td class="text-center align-middle">{{ OptLeaveType::find($leavesupl->leave_type_id)->leave_type_code }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesupl->date_time_start)->format('j M Y') }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesupl->date_time_end)->format('j M Y') }}</td>
					<td class="text-center align-middle">
							{{ $leavesupl->period_day }} day/s
							<?php $dur += $leavesupl->period_day ?>
					</td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="4" class="text-right">Total :</th>
					<th class="text-center">{{ $dur }} day/s</th>
				</tr>
			</tfoot>
		</table>
	@endif
	</div>

	<p>&nbsp;</p>
	<h4>Medical Certificate Unpaid Leave</h4>
	<div class="table-responsive">
	<?php
	$leavesmcs = HRLeave::where(function(Builder $query) {
							$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
						})
						->where('staff_id', $staff->id)
						->where('leave_type_id', 11)
						->get();
	$durm = 0;
	?>
	@if($leavesmcs->count())
		<table class="table table-sm table-hover" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center align-middle">ID</th>
					<th class="text-center align-middle">Leave Type</th>
					<th class="text-center align-middle">From</th>
					<th class="text-center align-middle">To</th>
					<th class="text-center align-middle">Duration</th>
				</tr>
			</thead>
			<tbody>
				@foreach($leavesmcs as $leavesmc)
				<tr>
					<td class="text-center align-middle">
						<a href="{{ route('hrleave.show', $leavesmc->id) }}" target="_blank">HR9-{{ str_pad( $leavesmc->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leavesmc->leave_year }}</a>
					</td>
					<td class="text-center align-middle">{{ OptLeaveType::find($leavesmc->leave_type_id)->leave_type_code }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesmc->date_time_start)->format('j M Y') }}</td>
					<td class="text-center align-middle">{{ \Carbon\Carbon::parse($leavesmc->date_time_end)->format('j M Y') }}</td>
					<td class="text-center align-middle">
							{{ $leavesmc->period_day }} day/s
							<?php $durm += $leavesmc->period_day ?>
					</td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="4" class="text-right">Total :</th>
					<th class="text-center">{{ $durm }} day/s</th>
				</tr>
			</tfoot>
		</table>
	@endif
	</div>

	<p>&nbsp;</p>
	<h4 class="align-items-center">Replacement Leave</h4>
	<div class="table-responsive">
		@if($staff->hasmanyleavereplacement()?->get()->count())
		<table class="table table-sm table-hover" style="font-size:12px;" id="replacementleave">
			<thead>
				<tr>
					<th>From</th>
					<th>To</th>
					<th>Location</th>
					<th>Remarks</th>
					<th>Total Day/s</th>
					<th>Leave Utilize</th>
					<th>Leave Balance</th>
					<th>Replacement Leave</th>
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
					<td class="table-responsive">
						<?php
						$leaves = $al->belongstomanyleave()->where(function(Builder $query) {
										$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
									})
									->get();
						?>
						@if($leaves->count())
							<table class="table table-hover table-sm">
								<thead>
									<tr>
										<th>Leave ID</th>
										<th>Duration</th>
									</tr>
								</thead>
								<tbody>
									<?php $total = 0; ?>
									@foreach($leaves as $key => $leave)
										<tr>
											<td>
												<a href="{{ route('hrleave.show', $leave->id) }}" target="_blank">
													HR9-{{ str_pad( $leave->leave_no, 5, "0", STR_PAD_LEFT ) }}/{{ $leave->leave_year }}
												</a>
											</td>
											<td>
												{{ $leave->period_day }} day/s
												<?php $total += $leave->period_day; ?>
											</td>
										</tr>
									@endforeach
								</tbody>
								<tfoot>
									<tr>
										<th>Total</th>
										<th>{{ $total }} day/s</th>
									</tr>
								</tfoot>
							</table>
						@endif
					</td>
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
	<p>&nbsp;</p>
	<h4 class="align-items-center">Disciplinary</h4>
	<div class="table-responsive">
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
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('.form-select').select2({
placeholder: '',
width: '100%',
allowClear: false,
closeOnSelect: true,
});

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
	"searching": false,
	"info": false,
	"paging": false,
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
	"responsive": true
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

/////////////////////////////////////////////////////////////////////////////////////////
// fullcalendar
var calendarEl = document.getElementById('calendar');
var calendar = new FullCalendar.Calendar(calendarEl, {
	aspectRatio: 1.0,
	height: 2000,
	// plugins: [multiMonthPlugin],
	initialView: 'multiMonthYear',
	// initialView: 'dayGridMonth',
	// multiMonthMaxColumns: 1,					// force a single column
	headerToolbar: {
		left: 'prev,next today',
		center: 'title',
		right: 'multiMonthYear,dayGridMonth,timeGridWeek'
	},
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
		$(info.el).tooltip({
		// var tooltip = new Tooltip(info.el, {
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

@endsection

@section('nonjquery')
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
