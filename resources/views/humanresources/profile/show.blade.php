@extends('layouts.app')

@section('content')
<?php
// load facade
use Illuminate\Database\Eloquent\Builder;

// load lib
use \Carbon\Carbon;

$emergencies = $profile->hasmanyemergency()->get();
$spouses = $profile->hasmanyspouse()->get();
$childrens = $profile->hasmanychildren()->get();
?>

<div class="container row align-items-start justify-content-center">

	<div class="col-sm-2 row">
		<div class="d-flex flex-column align-items-center">
			<img class="rounded-5" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
			<span style="font-size: 18px;"><b>ID: {{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</b></span>
		</div>
	</div>

	<div class="col-sm-12 row align-items-start justify-content-center">
		<h4>Staff Profile &nbsp; <a href="{{ route('profile.edit', $profile->id) }}" class="btn btn-sm btn-outline-secondary">Change Password</a></h4>
		<div class="col-sm-6">
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $profile->name }}</dd>
				<dt class="col-sm-5">Identity Card/Passport</dt>
				<dd class="col-sm-7">{{ $profile->ic }}</dd>
				<dt class="col-sm-5">Mobile Number</dt>
				<dd class="col-sm-7">{{ $profile->mobile }}</dd>
				<dt class="col-sm-5">Email</dt>
				<dd class="col-sm-7">{{ $profile->email }}</dd>
				<dt class="col-sm-5">Address</dt>
				<dd class="col-sm-7">
					<address>{{ $profile->address }}</address>
				</dd>
				<dt class="col-sm-5">Department</dt>
				<dd class="col-sm-7">{{ $profile->belongstomanydepartment()?->wherePivot('main', 1)->first()?->department }}</dd>
			</dl>
		</div>

		<div class="col-sm-6">
			<dl class="row">
				<dt class="col-sm-5">Category</dt>
				<dd class="col-sm-7">{{ $profile->belongstomanydepartment()?->wherePivot('main', 1)->first()?->belongstocategory->category }}</dd>
				<dt class="col-sm-5">Saturday Group</dt>
				<dd class="col-sm-7">{{ $profile->belongstorestdaygroup?->group }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Gender</dt>
				<dd class="col-sm-7">{{ $profile->belongstogender->gender }}</dd>
				<dt class="col-sm-5">Nationality</dt>
				<dd class="col-sm-7">{{ $profile->belongstonationality?->country }}</dd>
				<dt class="col-sm-5">Race</dt>
				<dd class="col-sm-7">{{ $profile->belongstorace?->race }}</dd>
				<dt class="col-sm-5">Religion</dt>
				<dd class="col-sm-7">{{ $profile->belongstoreligion?->religion }}</dd>
				<dt class="col-sm-5">Marital Status</dt>
				<dd class="col-sm-7">{{ $profile->belongstoreligion?->religion }}</dd>
				<dt class="col-sm-5">Join Date</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Confirm Date</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}</dd>
			</dl>
		</div>
	</div>

	<div class="col-sm-12 row align-items-start justify-content-center mt-3">
		<div class="col-sm-4">
			@if ($emergencies->count())
			<h4>Emergency Contact</h4>
			@foreach ($emergencies as $emergency)
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $emergency->contact_person }}</dd>
				<dt class="col-sm-5">Relationship</dt>
				<dd class="col-sm-7">{{ $emergency->belongstorelationship?->relationship }}</dd>
				<dt class="col-sm-5">Phone Number</dt>
				<dd class="col-sm-7">{{ $emergency->phone }}</dd>
				<dt class="col-sm-5">Address</dt>
				<dd class="col-sm-7">
					<address>{{ $emergency->address }}</address>
				</dd>
			</dl>
			@endforeach
			@endif
		</div>

		<div class="col-sm-4">
			@if ($spouses->count())
			<h4>Spouse</h4>
			@foreach ($spouses as $spouse)
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $spouse->spouse }}</dd>
				<dt class="col-sm-5">Identity Card/Passport</dt>
				<dd class="col-sm-7">{{ $spouse->id_card_passport }}</dd>
				<dt class="col-sm-5">Phone Number</dt>
				<dd class="col-sm-7">{{ $spouse->phone }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($spouse->dob)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Profession</dt>
				<dd class="col-sm-7">{{ $spouse->profession }}</dd>
			</dl>
			@endforeach
			@endif
		</div>

		<div class="col-sm-4">
			@if ($childrens->count())
			<h4>Children</h4>
			@foreach ($childrens as $children)
			<dl class="row">
				<dt class="col-sm-5">Name</dt>
				<dd class="col-sm-7">{{ $children->children }}</dd>
				<dt class="col-sm-5">Date Of Birth</dt>
				<dd class="col-sm-7">{{ \Carbon\Carbon::parse($children->dob)->format('d F Y') }}</dd>
				<dt class="col-sm-5">Gender</dt>
				<dd class="col-sm-7">{{ $children->belongstogender?->gender }}</dd>
				<dt class="col-sm-5">Health Condition</dt>
				<dd class="col-sm-7">{{ $children->belongstohealthstatus?->health_status }}</dd>
				<dt class="col-sm-5">Education Level</dt>
				<dd class="col-sm-7">{{ $children->belongstoeducationlevel?->education_level }}</dd>
			</dl>
			@endforeach
			@endif
		</div>
	</div>

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

			$daytype = $attend->belongstodaytype()->first();
			$outstation = $attend->belongstooutstation?->belongstocustomer?->customer;
			$overtime = $attend->belongstoovertime?->belongstoovertimerange?->total_time;

			if ($attend->in != NULL && $attend->in != '00:00:00') {
				$in = Carbon::parse($attend->in)->format('h:i a');
			}

			if ($attend->break != NULL && $attend->break != '00:00:00') {
				$break = Carbon::parse($attend->break)->format('h:i a');
			}

			if ($attend->resume != NULL && $attend->resume != '00:00:00') {
				$resume = Carbon::parse($attend->resume)->format('h:i a');
			}

			if ($attend->out != NULL && $attend->out != '00:00:00') {
				$out = Carbon::parse($attend->out)->format('h:i a');
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

			$work_hour = \App\Models\HumanResources\OptWorkingHour::where('option_working_hours.group', '=', $wh_group)
			->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
			->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
			->where('option_working_hours.category', '=', 3);

			dd($work_hour);
			?>

			<tr>
				<td class="text-center">
					{{ $attend->attend_date }}
				</td>
				<td class="text-center">
					{{ $daytype->daytype }}
				</td>
				<td class="text-center">
					{{ $in }}
				</td>
				<td class="text-center">
					{{ $break }}
				</td>
				<td class="text-center">
					{{ $resume }}
				</td>
				<td class="text-center">
					{{ $out }}
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
});}
);

@endsection