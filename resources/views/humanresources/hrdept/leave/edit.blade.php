@extends('layouts.app')

@section('content')
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

<?php
use \App\Models\Staff;
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;

$user = $hrleave->belongstostaff;
$userneedbackup = $user->belongstoleaveapprovalflow->backup_approval;
$setHalfDayMC = \App\Models\Setting::find(2)->active;
// dd($setHalfDayMC);
// checking for overlapped leave only for half day leave
// dd(\App\Helpers\UnavailableDateTime::unblockhalfdayleave($hrleave->belongstostaff->id, '2023-09-08'));
// dd($hrleave);

$staff = $user;
// dd([$staff, $user]);
$login = $staff->hasmanylogin()->where('active', 1)->get()->first();

$count = 0;
$supervisor_no = 0;
$hod_no = 0;
$director_no = 0;
$hr_no = 0;

$backup = $hrleave->hasmanyleaveapprovalbackup->first();
$supervisor = $hrleave->hasmanyleaveapprovalsupervisor->first();
$hod = $hrleave->hasmanyleaveapprovalhod->first();
$director = $hrleave->hasmanyleaveapprovaldir->first();
$hr = $hrleave->hasmanyleaveapprovalhr->first();

if ($supervisor) {
	$count++;
	$supervisor_no = $count;
}

if ($hod) {
	$count++;
	$hod_no = $count;
}

if ($director) {
	$count++;
	$director_no = $count;
}

if ($hr) {
	$count++;
	$hr_no = $count;
}

if ($count != 0) {
	$width = 100 / $count;
} else {
	$width = 100;
}

if ((\Carbon\Carbon::parse($hrleave->date_time_start)->format('H:i')) == '00:00') {
	$date_start = \Carbon\Carbon::parse($hrleave->date_time_start)->format('d F Y');
} else {
	$date_start = \Carbon\Carbon::parse($hrleave->date_time_start)->format('d F Y h:i a');
}

if ((\Carbon\Carbon::parse($hrleave->date_time_end)->format('H:i')) == '00:00') {
	$date_end = \Carbon\Carbon::parse($hrleave->date_time_end)->format('d F Y');
} else {
	$date_end = \Carbon\Carbon::parse($hrleave->date_time_end)->format('d F Y h:i a');
}

if ($hrleave->period_day !== 0.0 &&$hrleave->period_time == NULL) {
	$total_leave =$hrleave->period_day . ' Days';
} else {
	$total_leave =$hrleave->period_time;
}

if ($backup) {
	$backup_name = $backup->belongstostaff->name;

	if ($backup->created_at == $backup->updated_at) {
		$approved_date = '-';
	} else {
		$approved_date = \Carbon\Carbon::parse($backup->updated_at)->format('d F Y h:i a');
	}
} else {
	$backup_name = '-';
	$approved_date = '-';
}
?>
<div class="container row align-items-start justify-content-center">
	<div class="col-sm-12">
		@include('humanresources.hrdept.navhr')
		<h4>Leave Edit</h4>
		<div class="table-container">
			<div class="table">
				<div class="table-row header">
					<div class="table-cell" style="width: 40%; background-color: #99ff99;">IPMA INDUSTRY SDN.BHD.</div>
					<div class="table-cell" style="width: 60%; background-color: #e6e6e6;">LEAVE APPLICATION FORM</div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top" style="width: 25%;">STAFF ID : {{ @$login->username }}</div>
					<div class="table-cell-top" style="width: 75%;">NAME : {{ @$staff->name }}</div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top" style="width: 25%;">LEAVE NO : HR9-{{ @str_pad($hrleave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ $hrleave->leave_year }}</div>
					<div class="table-cell-top" style="width: 60%;">DATE : {{ @$date_start }} - {{ @$date_end }} </div>
					<div class="table-cell-top" style="width: 25%;">TOTAL : {{ @$total_leave }} </div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top text-wrap" style="width: 45%;">LEAVE TYPE : {{ $hrleave->belongstooptleavetype->leave_type_code }} ({{ $hrleave->belongstooptleavetype->leave_type }})</div>
					<div class="table-cell-top text-wrap" style="width: 55%;">REASON : {{ $hrleave->reason }} </div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					<div class="table-cell-top text-wrap" style="width: 60%;">BACKUP : {{ @$backup_name }}</div>
					<div class="table-cell-top" style="width: 40%;">DATE APPROVED : {{ @$approved_date }} </div>
				</div>
			</div>

		<?php
		use \App\Models\HumanResources\HRAttendance;
		use Illuminate\Database\Eloquent\Builder;

		$hrremarksattendance = HRAttendance::where(function (Builder $query) use ($hrleave){
												$query->whereDate('attend_date', '>=', $hrleave->date_time_start)
												->whereDate('attend_date', '<=', $hrleave->date_time_end);
											})
								->where('staff_id', $hrleave->staff_id)
								->where(function (Builder $query) {
									$query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
								})
								// ->ddrawsql();
								->get();
		?>
		@if($hrremarksattendance)
		<div class="table">
			@foreach($hrremarksattendance as $key => $valueble)
				<div class="table-row">
					<div class="table-cell-top" style="width: 100%;">REMARKS FROM ATTENDANCE : {{ $valueble->remarks }}<br/>HR REMARKS FROM ATTENDANCE : {{ $valueble->hr_remarks }}</div>
				</div>
			@endforeach
		</div>
		@endif





			<div class="table">
				<div class="table-row">
					<div class="table-cell-top text-center" style="width: 100%; background-color: #ffcc99; font-size: 18px;">SIGNATURE / APPROVALS</div>
				</div>
			</div>

			<div class="table">
				<div class="table-row">
					@for ($a = 1; $a <= $count; $a++)
						@if ($supervisor_no==$a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">SUPERVISOR</div>
						@elseif ($hod_no == $a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">HOD</div>
						@elseif ($director_no == $a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">DIRECTOR</div>
						@elseif ($hr_no == $a)
							<div class="table-cell-top text-center" style="width: {{ $width }}%; background-color: #f2f2f2; font-size: 18px;">HR</div>
						@endif
					@endfor
				</div>
			</div>

			<div class="table">
				<div class="table-row" style="height: 50px;">
					@for ($a = 1; $a <= $count; $a++)
						@if ($supervisor_no==$a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$supervisor->belongstostaff->name }}</div>
						@elseif ($hod_no == $a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hod->belongstostaff->name }}</div>
						@elseif ($director_no == $a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$director->belongstostaff->name }}</div>
						@elseif ($hr_no == $a)
							<div class="table-cell-top-bottom text-center text-decoration-underline text-wrap" style="width: {{ $width }}%; vertical-align: bottom;">{{ @$hr->belongstostaff->name }}</div>
						@endif
					@endfor
				</div>
				<div class="table-row">
					@for ($a = 1; $a <= $count; $a++)
						@if ($supervisor_no==$a)
							<div class="table-cell-top1 text-center">{{ @$supervisor->updated_at }}</div>
						@elseif ($hod_no == $a)
							<div class="table-cell-top1 text-center">{{ @$hod->updated_at }}</div>
						@elseif ($director_no == $a)
							<div class="table-cell-top1 text-center">{{ @$director->updated_at }}</div>
						@elseif ($hr_no == $a)
							<div class="table-cell-top1 text-center">{{ @$hr->updated_at }}</div>
						@endif
					@endfor
				</div>
			</div>
		</div>
	</div>

	<p>&nbsp;</p>

	<div class="col-sm-12 row justify-content-center align-items-start">
		{{ Form::model($hrleave, ['route' => ['hrleave.update', $hrleave], 'method' => 'PATCH', 'id' => 'form', 'autocomplete' => 'off', 'files' => true, 'data-toggle' => 'validator']) }}
		<h5>Edit Leave Application</h5>

		<div class="form-group row m-1{{ $errors->has('leave_id') ? 'has-error' : '' }}">
			{{ Form::label( 'leave_type_id', 'Leave Type : ', ['class' => 'col-sm-4 col-form-label'] ) }}
			<div class="col-sm-8">
				{{ Form::select('leave_type_id', \App\Models\HumanResources\OptLeaveType::pluck('leave_type', 'id'), @$value, ['id' => 'leave_id', 'class' => 'form-select form-select-sm']) }}
			</div>
		</div>

		<div class="form-group row m-1{{ $errors->has('reason') ? 'has-error' : '' }}">
			{{ Form::label( 'reason', 'Reason : ', ['class' => 'col-sm-4 col-form-label'] ) }}
			<div class="col-sm-8">
				{{ Form::textarea('reason', @$value, ['class' => 'form-control form-control-sm', 'id' => 'reason', 'placeholder' => 'Reason', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div id="wrapper" class="m-1">
		</div>

		<div class="form-group row m-1{{ $errors->has('amend_note') ? 'has-error' : '' }}">
			{{ Form::label( 'amend_note', 'Amend Note : ', ['class' => 'col-sm-4 col-form-label'] ) }}
			<div class="col-sm-8">
				{{ Form::textarea('amend_note', @$value, ['class' => 'form-control ', 'id' => 'amend_note', 'placeholder' => 'Amend Note', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div class="form-group m-1 row">
			<div class="col-sm-8 offset-sm-4">
				{!! Form::button('Submit Application', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
			</div>
		</div>
		{{ Form::close() }}
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#leave_id').select2({
	placeholder: 'Please choose',
	allowClear: true,
	closeOnSelect: true,
	width: '100%',
	ajax: {
		url: '{{ route('leaveType.leaveType') }}',
		// data: { '_token': '{!! csrf_token() !!}' },
		type: 'POST',
		dataType: 'json',
		data: function () {
			var data = {
				id: {{ $hrleave->belongstostaff->id }},
				_token: '{!! csrf_token() !!}',
			}
			return data;
		}
	},
});

/////////////////////////////////////////////////////////////////////////////////////////
// start setting up the leave accordingly.
/////////////////////////////////////////////////////////////////////////////////////////
//  global variable : ajax to get the unavailable date
var data2 = $.ajax({
	url: "{{ route('leavedate.unavailabledate') }}",
	type: "POST",
	data : {
				id: {{ $hrleave->belongstostaff->id }},
				type: 1,
				_token: '{!! csrf_token() !!}',
			},
	dataType: 'json',
	global: false,
	async: false,
	success: function (response) {
		// you will get response from your php page (what you echo or print)
		// return response;
		var arrItems = [];              		// The array to store JSON data.
		$.each(response, function (index, value) {
			arrItems.push(value);       		// Push the value inside the array.
		});
		return arrItems;
	},
	error: function(jqXHR, textStatus, errorThrown) {
		console.log(textStatus, errorThrown);
	}
}).responseText;

// this is how u cange from json to array type data
var data = $.parseJSON( data2 );

var data3 = $.ajax({
	url: "{{ route('leavedate.unavailabledate') }}",
	type: "POST",
	data : {
				id: {{ $hrleave->belongstostaff->id }},
				type: 2,
				_token: '{!! csrf_token() !!}',
			},
	dataType: 'json',
	global: false,
	async: false,
	success: function (response) {
		// you will get response from your php page (what you echo or print)
		// return response;
		var arrItems = [];              		// The array to store JSON data.
		$.each(response, function (index, value) {
			arrItems.push(value);       		// Push the value inside the array.
		});
		return arrItems;
	},
	error: function(jqXHR, textStatus, errorThrown) {
		console.log(textStatus, errorThrown);
	}
}).responseText;

// this is how u change from json to array type data
var data4 = $.parseJSON( data3 );

/////////////////////////////////////////////////////////////////////////////////////////
// checking for overlapp leave on half day (if it is turn on)
var data10 = $.ajax({
	url: "{{ route('unblockhalfdayleave.unblockhalfdayleave') }}",
	type: "POST",
	data: {
			id: {{ $hrleave->belongstostaff->id }},
			_token: '{!! csrf_token() !!}',
		},
	dataType: 'json',
	global: false,
	async:false,
	success: function (response) {
		// you will get response from your php page (what you echo or print)
		return response;
	},
	error: function(jqXHR, textStatus, errorThrown) {
		console.log(textStatus, errorThrown);
	}
}).responseText;

// convert data10 into json
var objtime = $.parseJSON( data10 );

// console.log(objtime);

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
$(document).ready(function(){
	if ($('#leave_id').val() == '9') {													// if TF
		// console.log($('#leave_id').val());
		// insert tf leave here
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- time off -->
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'Date : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', Carbon::parse($hrleave->date_time_start)->format('Y-m-d'), ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'Date : ', 'autocomplete' => 'off']) }}' +
						'{{ Form::hidden('date_time_end', Carbon::parse($hrleave->date_time_start)->format('Y-m-d'), ['id' => 'to']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'Time : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8">' +
							'<div class="form-group row time">' +
								'<div class="col-sm-4" style="position: relative">' +
									'{{ Form::text('time_start', Carbon::parse($hrleave->date_time_start)->format('H:i:s'), ['class' => 'form-control form-control-sm', 'id' => 'start', 'placeholder' => 'Time From : ', 'autocomplete' => 'off']) }}' +
								'</div>' +
								'<div class="col-sm-4" style="position: relative">' +
									'{{ Form::text('time_end', Carbon::parse($hrleave->date_time_end)->format('H:i:s'), ['class' => 'form-control form-control-sm', 'id' => 'end', 'placeholder' => 'Time To : ', 'autocomplete' => 'off']) }}' +
								'</div>' +
							'</div>' +
					'</div>' +
				'</div>' +
				@if( $userneedbackup == 1 )
				@if($hrleave->leave_type_id != 2 || $hrleave->leave_type_id != 11)

				@endif
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'{{ Form::select('staff_id', Staff::where('active', 1)->pluck('name', 'id'), $hrleave->hasmanyleaveapprovalbackup()->first()?->staff_id??NULL, ['id' => 'backupperson', 'class' => 'form-select form-select-sm', 'placeholder' => 'Please Choose']) }}' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row m-1 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Documents : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input rounded', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +
			'</div>'

		);
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
	} else {																			// other than TF
		// console.log('else');
		console.log(moment('{{ Carbon::parse($hrleave->date_time_start) }}').format('HH:mm:ss'));
		var datenow = '{{ Carbon::parse($hrleave->date_time_start)->format('Y-m-d') }}';

		var data1 = $.ajax({
			url: "{{ route('leavedate.timeleave') }}",
			type: "POST",
			data: {
					date: datenow,
					_token: '{!! csrf_token() !!}',
					id: {{ $hrleave->belongstostaff->id }},
			},
			dataType: 'json',
			global: false,
			async: false,
			success: function (response) {
				// you will get response from your php page (what you echo or print)
				return response;
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		}).responseText;

		// convert data1 into json
		var obj = $.parseJSON( data1 );

		$('#wrapper').append(
			'<div id="remove">' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', Carbon::parse($hrleave->date_time_start)->format('Y-m-d'), ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', Carbon::parse($hrleave->date_time_end)->format('Y-m-d'), ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					@if($hrleave->period_day <= 1)
						'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
						'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
							'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
								'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
								'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
							'</div>' +
							'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
								'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
								'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
							'</div>' +
						'</div>' +
						'<div class="form-group col-sm-8 offset-sm-4 mb-1 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							@if($hrleave->period_day <= 0.5)
								'<div class="form-check form-check-inline removetest">' +
									'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am"  {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
									'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
									'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm"  {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
									'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'</div>' +
							@endif
						'</div>' +
					@endif

				'</div>' +
				'@if( $userneedbackup == 1 )' +
				'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
					'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 backup">' +
						'{{ Form::select('staff_id', Staff::where('active', 1)->pluck('name', 'id'), $hrleave->hasmanyleaveapprovalbackup()->first()?->staff_id??NULL, ['id' => 'backupperson', 'class' => 'form-select form-select-sm', 'placeholder' => 'Please Choose']) }}' +
					'</div>' +
				'</div>' +
				'@endif' +
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('documentsupport') ? 'has-error' : '' }}">' +
					'<div class="offset-sm-4 col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input ', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		);
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am"  {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm"  {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
					if( moment('{{ Carbon::parse($hrleave->date_time_start)->format('H:i:s') }}').isSame(moment(obj.time_start_am, 'HH:mm:ss')) ) {
						console.log('ppagi');
						$('#am').prop('checked', true);
					} else {
						console.log('ptg');
						$('#pm').prop('checked', true);
					}
				}
			}
		});

		if( moment('{{ Carbon::parse($hrleave->date_time_start)->format('H:i:s') }}').isSame(moment(obj.time_start_am, 'HH:mm:ss')) ) {
			console.log('ppagi');
			$('#am').prop('checked', true);
		} else {
			console.log('ptg');
			$('#pm').prop('checked', true);
		}

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
			}
		});
	}
	// start date
	$('#from').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fa-regular fa-circle-up fa-beat",
			down: "fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format:'YYYY-MM-DD',
		useCurrent: false,
		// minDate: moment().format('YYYY-MM-DD'),
		// disabledDates: data,
	})
	.on('dp.change dp.update', function(e) {
		// $('#form').bootstrapValidator('revalidateField', 'date_time_start');
		$('#to').datetimepicker('minDate', $('#from').val());

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
								date: datenow,
								_token: '{!! csrf_token() !!}',
								id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
	});
	// end date from

	// end date
	$('#to').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fa-regular fa-circle-up fa-beat",
			down: "fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format:'YYYY-MM-DD',
		useCurrent: false,
		// minDate: moment().format('YYYY-MM-DD'),
		// disabledDates: data,
	})
	.on('dp.change dp.update', function(e) {
		// $('#to').bootstrapValidator('revalidateField', 'date_time_start');
		$('#from').datetimepicker('maxDate', $('#to').val());

		if($('#from').val() === $('#to').val()) {
			if( $('.removehalfleave').length === 0) {

				////////////////////////////////////////////////////////////////////////////////////////
				// checking half day leave
				var d = false;
				var itime_start = 0;
				var itime_end = 0;
				$.each(objtime, function() {
				// console.log(this.date_half_leave);
					if(this.date_half_leave == $('#from').val()) {
						return [d = true, itime_start = this.time_start, itime_end = this.time_end];
					}
				});
				// console.log(d);
				if(d === true) {
					$('#wrapperday').append(
						'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
						'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
							'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
								'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
								'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
							'</div>' +
							'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
								'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
								'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
							'</div>' +
						'</div>' +
						'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
					var datenow =$('#from').val();

					var data1 = $.ajax({
						url: "{{ route('leavedate.timeleave') }}",
						type: "POST",
						data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
						},
						dataType: 'json',
						global: false,
						async:false,
						success: function (response) {
							// you will get response from your php page (what you echo or print)
							return response;
						},
						error: function(jqXHR, textStatus, errorThrown) {
							console.log(textStatus, errorThrown);
						}
					}).responseText;

					// convert data1 into json
					var obj = $.parseJSON( data1 );

					var checkedam = 'checked';
					var checkedpm = 'checked';
					if(obj.time_start_am == itime_start) {
						var toggle_time_start_am = 'disabled';
						var checkedam = '';
						var checkedpm = 'checked';
					}

					if(obj.time_start_pm == itime_start) {
						var toggle_time_start_pm = 'disabled';
						var checkedam = 'checked';
						var checkedpm = '';
					}
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

				} else {
					$('#wrapperday').append(
						'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
						'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
							'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
								'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
								'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
							'</div>' +
							'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
								'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
								'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
							'</div>' +
						'</div>' +
						'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
				}
			}
		}
		if($('#from').val() !== $('#to').val()) {
			$('.removehalfleave').remove();
			$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
			$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
		}
	});

	// time start
	$('#start').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fas fa-regular fa-circle-up fa-beat",
			down: "fas fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format: 'h:mm A',
		// enabledHours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
	})
	.on('dp.change dp.update', function(e){
		// $('#form').bootstrapValidator('revalidateField', 'time_start');
		// $('#end').datetimepicker('minDate', moment($('#start').val(), 'h:mm A'));
	});

	// time end
	$('#end').datetimepicker({
		icons: {
			time: "fas fas-regular fa-clock fa-beat",
			date: "fas fas-regular fa-calendar fa-beat",
			up: "fas fa-regular fa-circle-up fa-beat",
			down: "fas fa-regular fa-circle-down fa-beat",
			previous: 'fas fas-regular fa-arrow-left fa-beat',
			next: 'fas fas-regular fa-arrow-right fa-beat',
			today: 'fas fas-regular fa-calenday-day fa-beat',
			clear: 'fas fas-regular fa-broom-wide fa-beat',
			close: 'fas fas-regular fa-rectangle-xmark fa-beat'
		},
		format: 'h:mm A',
		// enabledHours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
	})
	.on('dp.change dp.update', function(e){
		// $('#form').bootstrapValidator('revalidateField', 'time_end');
		// $('#start').datetimepicker('minDate', moment($('#end').val(), 'h:mm A'));
	});

	//enable select 2 for backup
	$('#backupperson').select2({
		placeholder: 'Please Choose',
		width: '100%',
		ajax: {
			url: '{{ route('backupperson') }}',
			// data: { '_token': '{!! csrf_token() !!}' },
			type: 'POST',
			dataType: 'json',
			data: function (params) {
				var query = {
					id: {{ $hrleave->belongstostaff->id }},
					_token: '{!! csrf_token() !!}',
					date_from: $('#from').val(),
					date_to: $('#to').val(),
				}
				return query;
			}
		},
		allowClear: true,
		closeOnSelect: true,
	});
});



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
// start here when user start to select the leave type option
$('#leave_id').on('change', function() {
	$selection = $(this).find(':selected');
	// console.log($selection);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// annual leave & UPL
	if ($selection.val() == '1' || $selection.val() == '3') {
		$('#remove').remove();
		if($selection.val() == '3') {
			$('#wrapper').append(
				'<div id="remove">' +
					<!-- annual leave -->
					'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
						'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 datetime" style="position: relative">' +
							'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +
					'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
						'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 datetime" style="position: relative">' +
							'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +
					'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
						'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>' +
					'</div>' +
					@if( $userneedbackup == 1 )
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
					@endif
					'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
						'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
						'<div class="col-sm-8 supportdoc">' +
							'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
						'</div>' +
					'</div>' +
					'<div class="form-group row m-1 {{ $errors->has('documentsupport') ? 'has-error' : '' }}">' +
						'<div class="offset-sm-4 col-sm-8 form-check suppdoc">' +
							'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input ', 'id' => 'suppdoc']) }}' +
							'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>' +
						'</div>' +
					'</div>' +
				'</div>'
			);
		} else {
			$('#wrapper').append(
				'<div id="remove">' +
					<!-- annual leave -->
					'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
						'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 datetime" style="position: relative">' +
							'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +
					'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
						'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 datetime" style="position: relative">' +
							'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +
					'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
						'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>' +
					'</div>' +
					@if( $userneedbackup == 1 )
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
					@endif
				'</div>'
			);
		}

		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		if($selection.val() == '3') {
			$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
			$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));
		}

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// start date
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fa-regular fa-circle-up fa-beat",
				down: "fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// minDate: moment().format('YYYY-MM-DD'),
			// disabledDates: data,
			// daysOfWeekDisabled: [0],
			// minDate: data[1],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			// console.log(minDaten);
			$('#to').datetimepicker('minDate', minDaten);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
								date: datenow,
								_token: '{!! csrf_token() !!}',
								id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// minDate: moment().format('YYYY-MM-DD'),
			// disabledDates:data,
			//daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
								date: datenow,
								_token: '{!! csrf_token() !!}',
								id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date
		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
						date: datenow,
						_token: '{!! csrf_token() !!}',
						id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '2') {

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- mc leave -->
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				@if($setHalfDayMC == 1)
				'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +
				@endif
				@if( $userneedbackup == 99 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row m-1 {{ $errors->has('documentsupport') ? 'has-error' : '' }}">' +
					'<div class="offset-sm-4 col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input ', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		);

		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-circle-up fa-beat",
				down: "fas fas-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: true,
			// disabledDates: data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
			// minDate: data[1],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			@if($setHalfDayMC == 1)
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
								date: datenow,
								_token: '{!! csrf_token() !!}',
								id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
			@endif
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-circle-up fa-beat",
				down: "fas fas-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: true,
			// disabledDates: data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
			// minDate: data[1],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			@if($setHalfDayMC == 1)
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
			@endif
		});
		// end date

		@if($setHalfDayMC == 1)
		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am"{{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm"{{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		@endif
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// replacement leave
<?php
$oi = $hrleave->belongstostaff->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->get();
?>
	if ($selection.val() == '4') {
		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				'<div class="form-group row m-1 {{ $errors->has('leave_id') ? 'has-error' : '' }}">' +
					'{{ Form::label('nrla', 'Please Choose Your Replacement Leave : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 nrl">' +
						'<p>Total Replacement Leave = {{ $oi->sum('leave_balance') }} days</p>' +
						'<select name="id" id="nrla" class="form-select form-select-sm">' +
							'<option value="">Please select</option>' +
						@foreach( $oi as $po )
							'<option value="{{ $po->id }}" data-nrlbalance="{{ $po->leave_balance }}">On ' + moment( '{{ $po->date_start }}', 'YYYY-MM-DD' ).format('ddd Do MMM YYYY') + ', your leave balance = {{ $po->leave_balance }} day</option>' +
						@endforeach
						'</select>' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +
				@if( $userneedbackup == 1 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
			'</div>'
		);

		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="id"]'));
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));


		/////////////////////////////////////////////////////////////////////////////////////////
		// enable select2 on nrla
		$('#nrla').select2({
			placeholder: 'Please select',
			width: '100%',
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable select2
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// minDate: moment().format('YYYY-MM-DD'),
			// disabledDates: data,
			// daysOfWeekDisabled: [0],
			// minDate: data[1],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			// console.log(minDaten);
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 m-1 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// minDate: moment().format('YYYY-MM-DD'),
			// disabledDates:data,
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am"{{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm"{{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		// $('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				console.log( $('#nrla option:selected').data('nrlbalance') );
				if( $('#nrla option:selected').data('nrlbalance') == 0.5 ) {

					// especially for select 2, if no select2, remove change()
					$('#nrla option:selected').prop('selected', false).change();
					// $('#nrla').val('').change();
				}
				$('.removetest').remove();
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// checking for half day click but select for 1 full day
		$('#nrla').change(function() {
			selectedOption = $('option:selected', this);
			$('#form').bootstrapValidator('revalidateField', 'leave_id');
			var nrlbal = selectedOption.data('nrlbalance');
			if (nrlbal == 0.5) {
				// make sure from and to date got value
				$('#from').val(moment().add(3, 'days').format('YYYY-MM-DD'));
				$('#to').val(moment().add(3, 'days').format('YYYY-MM-DD'));

				$('#radio2').prop('checked', true);
				// checking so there is no double

				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			} else {
				if( nrlbal != 0.5 ) {
					$('#radio1').prop('checked', true);
					$('.removetest').remove();
				}
			}
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// maternity leave
	if ($selection.val() == '7') {

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- maternity leave -->
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +

				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
			@if( $userneedbackup == 1 )
				'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
					'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 backup">' +
						'<select name="staff_id" id="backupperson" class="form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
					'</div>' +
				'</div>' +
			@endif
			'</div>'
		);


		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		//add bootstrapvalidator
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="leave_id"]'));
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ \Auth::user()->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			minDate: moment().format('YYYY-MM-DD'),
			disabledDates:data,
			//daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDate = $('#from').val();
			$('#to').datetimepicker('minDate', moment( minDate, 'YYYY-MM-DD').add(59, 'days').format('YYYY-MM-DD') );
			$('#to').val( moment( minDate, 'YYYY-MM-DD').add(59, 'days').format('YYYY-MM-DD') );
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			minDate: moment().format('YYYY-MM-DD'),
			disabledDates:data,
			//daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();

			// $('#from').datetimepicker('maxDate', moment( maxDate, 'YYYY-MM-DD').subtract(59, 'days').format('YYYY-MM-DD'));
			// $('#from').val( moment( maxDate, 'YYYY-MM-DD').subtract(59, 'days').format('YYYY-MM-DD') );
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '5' || $selection.val() == '6') {		// el-al and el-upl

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- emergency leave -->
				'<div class="form-group row m-1{{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1{{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1{{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
					'<div class="col-sm-8 removehalfleave" id="halfleave">' +
					'</div>' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +
				@if( $userneedbackup == 1 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1{{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Document : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input rounded', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		);
		/////////////////////////////////////////////////////////////////////////////////////////
		//add bootstrapvalidator
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="leave_id"]'));
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates:data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			@if( $userneedbackup == 1 )
			// enable backup if date from is greater or equal than today.
			// cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(
						'<div class="form-group row {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
							'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
							'<div class="col-sm-8 backup">' +
								'<select name="staff_id" id="backupperson" class="form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					$('#backupperson').select2({
						placeholder: 'Please Choose',
						width: '100%',
						ajax: {
							url: '{{ route('backupperson') }}',
							// data: { '_token': '{!! csrf_token() !!}' },
							type: 'POST',
							dataType: 'json',
							data: function (params) {
								var query = {
									id: {{ $hrleave->belongstostaff->id }},
									_token: '{!! csrf_token() !!}',
									date_from: $('#from').val(),
									date_to: $('#to').val(),
								}
								return query;
							}
						},
						allowClear: true,
						closeOnSelect: true,
					});
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			@endif
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates:data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = jQuery.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
			}
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '9') { // time off

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- time off -->
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'Date : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'Date : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'Time : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8">' +
							'<div class="form-row row time">' +
								'<div class="col-sm-4 m-1" style="position: relative">' +
									'{{ Form::text('time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'start', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
								'</div>' +
								'<div class="col-sm-4 m-1" style="position: relative">' +
									'{{ Form::text('time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'end', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
								'</div>' +
							'</div>' +
					'</div>' +
				'</div>' +
				@if( $userneedbackup == 1 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Documents : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input rounded', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		);
		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		//add bootstrapvalidator
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fa-regular fa-circle-up",
				down: "fas fa-regular fa-circle-down",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates:data,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change ', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');

			@if( $userneedbackup == 1 )
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(
						'<div class="form-group row {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
							'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
							'<div class="col-sm-8 backup">' +
								'<select name="staff_id" id="backupperson" class="form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					$('#backupperson').select2({
						placeholder: 'Please Choose',
						width: '100%',
						ajax: {
							url: '{{ route('backupperson') }}',
							// data: { '_token': '{!! csrf_token() !!}' },
							type: 'POST',
							dataType: 'json',
							data: function (params) {
								var query = {
									id: {{ $hrleave->belongstostaff->id }},
									_token: '{!! csrf_token() !!}',
									date_from: $('#from').val(),
									date_to: $('#to').val(),
								}
								return query;
							}
						},
						allowClear: true,
						closeOnSelect: true,
					});
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			@endif
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// time start
		// need to get working hour for each user
		// lazy to implement this 1. :P
		// moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a')
		// moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a')
		// moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a')
		// moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a')

		$('#start').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fa-regular fa-circle-up fa-beat",
				down: "fas fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format: 'h:mm A',
			// enabledHours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
		})
		.on('dp.change dp.update', function(e){
			$('#form').bootstrapValidator('revalidateField', 'time_start');
			// $('#end').datetimepicker('minDate', moment($('#start').val(), 'h:mm A'));
		});

		$('#end').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fa-regular fa-circle-up fa-beat",
				down: "fas fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format: 'h:mm A',
			// enabledHours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
		})
		.on('dp.change dp.update', function(e){
			$('#form').bootstrapValidator('revalidateField', 'time_end');
			// $('#start').datetimepicker('minDate', moment($('#end').val(), 'h:mm A'));
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($selection.val() == '11') {				// mc-upl

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- mc leave -->
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				@if($setHalfDayMC == 1)
				'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +
				@endif
				@if( $userneedbackup == 1 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Document : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input rounded', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		);

		//add bootstrapvalidator
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fa-regular fa-circle-up fa-beat",
				down: "fas fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates: data4,
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			@if($setHalfDayMC == 1)
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave " id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" disabled="disabled">' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" checked="checked">' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			@endif
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			// for backup person based on from date
			@if( $userneedbackup == 1 )
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(
						'<div class="form-group row {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
							'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
							'<div class="col-sm-8 backup">' +
								'<select name="staff_id" id="backupperson" class="form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					$('#backupperson').select2({
						placeholder: 'Please Choose',
						width: '100%',
						ajax: {
							url: '{{ route('backupperson') }}',
							// data: { '_token': '{!! csrf_token() !!}' },
							type: 'POST',
							dataType: 'json',
							data: function (params) {
								var query = {
									id: {{ $hrleave->belongstostaff->id }},
									_token: '{!! csrf_token() !!}',
									date_from: $('#from').val(),
									date_to: $('#to').val(),
								}
								return query;
							}
						},
						allowClear: true,
						closeOnSelect: true,
					});
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			@endif
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fa-regular fa-circle-up fa-beat",
				down: "fas fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			useCurrent: false,
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates:data4,
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			@if($setHalfDayMC == 1)
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave " id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" checked="checked">' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" >' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			@endif
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		@if( $userneedbackup == 1 )
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});
		@endif
		/////////////////////////////////////////////////////////////////////////////////////////
		@if($setHalfDayMC == 1)
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = jQuery.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
			}
		});
		@endif
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// el replacement leave
<?php
$oi = $hrleave->belongstostaff->hasmanyleavereplacement()->where('leave_balance', '<>', 0)->get();
?>
	if ($selection.val() == '10') {

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				'<div class="form-group row m-1 {{ $errors->has('leave_id') ? 'has-error' : '' }}">' +
					'{{ Form::label('nrla', 'Please Choose Your Replacement Leave : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 nrl">' +
						'<p>Total Replacement Leave = {{ $oi->sum('leave_balance') }} days</p>' +
						'<select name="id" id="nrla" class="form-select form-select-sm">' +
							'<option value="">Please select</option>' +
						@foreach( $oi as $po )
							'<option value="{{ $po->id }}" data-nrlbalance="{{ $po->leave_balance }}">On ' + moment( '{{ $po->date_start }}', 'YYYY-MM-DD' ).format('ddd Do MMM YYYY') + ', your leave balance = {{ $po->leave_balance }} day</option>' +
						@endforeach
						'</select>' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +
				@if( $userneedbackup == 1 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}" id="backupremove">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Document : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		);

		/////////////////////////////////////////////////////////////////////////////////////////
		// more option
		$('#form').bootstrapValidator('addField', $('.nrl').find('[name="leave_id"]'));
		@if( $userneedbackup == 1 )
			$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable select2
		$('#nrla').select2({ placeholder: 'Please select', 	width: '100%',
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable datetime for the 1st one
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates: data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
			// minDate: data[1],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
									'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
									'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			@if( $userneedbackup == 1 )
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(
						'<div class="form-group row {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
							'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
							'<div class="col-sm-8 backup">' +
								'<select name="staff_id" id="backupperson" class="form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					$('#backupperson').select2({
						placeholder: 'Please Choose',
						width: '100%',
						ajax: {
							url: '{{ route('backupperson') }}',
							// data: { '_token': '{!! csrf_token() !!}' },
							type: 'POST',
							dataType: 'json',
							data: function (params) {
								var query = {
									id: {{ $hrleave->belongstostaff->id }},
									_token: '{!! csrf_token() !!}',
									date_from: $('#from').val(),
									date_to: $('#to').val(),
								}
								return query;
							}
						},
						allowClear: true,
						closeOnSelect: true,
					});
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			@endif
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates:data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		// $('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				console.log( $('#nrla option:selected').data('nrlbalance') );
				if( $('#nrla option:selected').data('nrlbalance') == 0.5 ) {

					// especially for select 2, if no select2, remove change()
					$('#nrla option:selected').prop('selected', false).change();
					// $('#nrla').val('').change();
				}
				$('.removetest').remove();
			}
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// checking for half day click but select for 1 full day
		$('#nrla').change(function() {
			selectedOption = $('option:selected', this);
			$('#form').bootstrapValidator('revalidateField', 'leave_id');
			var nrlbal = selectedOption.data('nrlbalance');
			if (nrlbal == 0.5) {
				// make sure from and to date got value
				$('#from').val(moment().add(3, 'days').format('YYYY-MM-DD'));
				$('#to').val(moment().add(3, 'days').format('YYYY-MM-DD'));

				$('#radio2').prop('checked', true);
				// checking so there is no double

				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			} else {
				if( nrlbal != 0.5 ) {
					$('#radio1').prop('checked', true);
					$('.removetest').remove();
				}
			}
		});
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// S-UPL
	if ($selection.val() == '12') {

		$('#remove').remove();
		$('#wrapper').append(
			'<div id="remove">' +
				<!-- annual leave -->
				'<div class="form-group row m-1 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
					'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
					'</div>' +
				'</div>' +
				@if( $userneedbackup == 1 )
				'<div id="backupwrapper">' +
					'<div class="form-group row m-1 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
				'</div>' +
				@endif
				'<div class="form-group row m-1 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
					'<div class="col-sm-8 supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row m-1 {{ $errors->has('akuan') ? 'has-error' : '' }}">' +
					'{{ Form::label('suppdoc', 'Supporting Document : ', ['class' => 'col-sm-4 col-form-label']) }}' +
					'<div class="col-sm-8 form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input rounded', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Document</strong> within a period of  <strong>3 Days</strong> upon return.</label>' +
					'</div>' +
				'</div>' +
			'</div>'
			);
		/////////////////////////////////////////////////////////////////////////////////////////
		// add more option
		//add bootstrapvalidator
		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
		$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
		$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
		$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			allowClear: true,
			closeOnSelect: true,
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ $hrleave->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// start date
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fa-regular fa-circle-up fa-beat",
				down: "fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates: data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// minDate: data[1],
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			$('#to').datetimepicker('minDate', minDaten);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}

			@if( $userneedbackup == 1 )
			// enable backup if date from is greater or equal than today.
			//cari date now dulu
			if( $('#from').val() >= moment().format('YYYY-MM-DD') ) {
				// console.log( moment().add(1, 'days').format('YYYY-MM-DD') );
				// console.log($( '#rembackup').children().length + ' <= rembackup length' );
				if( $('#backupwrapper').children().length == 0 ) {
					$('#backupwrapper').append(
						'<div class="form-group row {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
							'{{ Form::label('backupperson', 'Backup Person : ', ['class' => 'col-sm-4 col-form-label']) }}' +
							'<div class="col-sm-8 backup">' +
								'<select name="staff_id" id="backupperson" class="form-control form-select form-select-sm" placeholder="Please choose" autocomplete="off"></select>' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
					$('#backupperson').select2({
						placeholder: 'Please Choose',
						width: '100%',
						ajax: {
							url: '{{ route('backupperson') }}',
							// data: { '_token': '{!! csrf_token() !!}' },
							type: 'POST',
							dataType: 'json',
							data: function (params) {
								var query = {
									id: {{ $hrleave->belongstostaff->id }},
									_token: '{!! csrf_token() !!}',
									date_from: $('#from').val(),
									date_to: $('#to').val(),
								}
								return query;
							}
						},
						allowClear: true,
						closeOnSelect: true,
					});
				}
			} else {
				$('#form').bootstrapValidator('removeField', $('.backup').find('[name="staff_id"]'));
				$('#backupwrapper').children().remove();
			}
			@endif
		});

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			// disabledDates:data4,
			// minDate: moment().format('YYYY-MM-DD'),
			// daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);

			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
					// console.log(d);
					if(d === true) {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {
									date: datenow,
									_token: '{!! csrf_token() !!}',
									id: {{ $hrleave->belongstostaff->id }}
							},
							dataType: 'json',
							global: false,
							async:false,
							success: function (response) {
								// you will get response from your php page (what you echo or print)
								return response;
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus, errorThrown);
							}
						}).responseText;

						// convert data1 into json
						var obj = $.parseJSON( data1 );

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
						$('#wrappertest').append(
							'<div class="form-check form-check-inline removetest">' +
								'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
								'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
								'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
								'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					} else {
						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-8 removehalfleave mb-1" id="halfleave">' +
								'<div class="form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="removehalfleave" {{ ($hrleave->period_day == 1)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="removehalfleave" {{ ($hrleave->period_day == 0.5)?'checked=checked':NULL }}>' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-1 removehalfleave']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
						$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));
					}
					////////////////////////////////////////////////////////////////////////////////////////
					// end checking half day leave
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('.removehalfleave').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
		// end date

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
							date: datenow,
							_token: '{!! csrf_token() !!}',
							id: {{ $hrleave->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = jQuery.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" {{ ($hrleave->half_type_id == 1)?'checked=checked':NULL }}>' +
							'<label for="am" class="form-check-label m-1">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" {{ ($hrleave->half_type_id == 2)?'checked=checked':NULL }}>' +
							'<label for="pm" class="form-check-label m-1">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
			}
		});
	}
});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// validator
$(document).ready(function() {
	$('#form').bootstrapValidator({
		feedbackIcons: {
			valid: '',
			invalid: '',
			validating: ''
		},
		fields: {
			leave_type_id: {
				validators: {
					notEmpty: {
						message: 'Please choose'
					},
				}
			},
			reason: {
				validators: {
					notEmpty: {
						message: 'Please insert your reason'
					},
					callback: {
						message: 'The reason must be less than 200 characters long',
						callback: function(value, validator, $field) {
							var div  = $('<div/>').html(value).get(0),
							text = div.textContent || div.innerText;
							return text.length <= 200;
						},
					},
				}
			},
			akuan: {
				validators: {
					notEmpty: {
						message: 'Please click this as an acknowledgement'
					}
				}
			},
			date_time_start: {
				validators: {
					notEmpty : {
						message: 'Please insert date start'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date. '
					},
				}
			},
			date_time_end: {
				validators: {
					notEmpty : {
						message: 'Please insert date end'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: 'The value is not a valid date. '
					},
				}
			},
			time_start: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-6]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			time_end: {
				validators: {
					notEmpty: {
						message: 'Please insert time',
					},
					regexp: {
						regexp: /^([1-6]|[8-9]|1[0-2]):([0-5][0-9])\s([A|P]M|[a|p]m)$/i,
						message: 'The value is not a valid time',
					}
				}
			},
			id: {
				validators: {
					notEmpty: {
						message: 'Please select',
					},
				}
			},
			leave_cat: {
				validators: {
					notEmpty: {
						message: 'Please select leave category',
					},
				}
			},
			staff_id: {
				validators: {
					// notEmpty: {
					// 	message: 'Please choose'
					// }
				}
			},
			amend_note: {
				validators: {
					notEmpty: {
						message: 'Please insert note'
					}
				}
			},
			document: {
				validators: {
					file: {
						extension: 'jpeg,jpg,png,bmp,pdf,doc,docx',											// no space
						type: 'image/jpeg,image/png,image/bmp,application/pdf,application/msword',			// no space
						maxSize: 5242880,	// 5120 * 1024,
						message: 'The selected file is not valid. Please use jpeg, jpg, png, bmp, pdf or doc and the file is below than 5MB. '
					},
				}
			},
			// documentsupport: {
			// 	validators: {
			// 		notEmpty: {
			// 			message: 'Please click this as an aknowledgement.'
			// 		},
			// 	}
			// },
		}
	})
	.find('[name="reason"]')
	// .ckeditor()
	// .editor
		.on('change', function() {
			// Revalidate the bio field
		$('#form').bootstrapValidator('revalidateField', 'reason');
		// console.log($('#reason').val());
	})
	;
});

/////////////////////////////////////////////////////////////////////////////////////////
@endsection

@section('nonjquery')
	function printPage() {
		window.print();
	}

	function back() {
		window.history.back();
	}

@endsection
