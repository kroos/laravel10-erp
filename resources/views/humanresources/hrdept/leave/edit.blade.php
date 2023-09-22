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
$login = $staff->hasmanylogin()->get()->first();

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
<div class="col-sm-12 row">
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

	<p>&nbsp;</p>

	<div class="d-flex justify-content-center align-items-start">
		{{ Form::model($hrleave, ['route' => ['hrleave.update', $hrleave->id], 'id' => 'form', 'autocomplete' => 'off', 'files' => true,  'data-toggle' => 'validator']) }}
		<h5>Leave Application Edit</h5>

		<div class="form-group row {{ $errors->has('leave_id') ? 'has-error' : '' }}">
			{{ Form::label( 'leave_type_id', 'Leave Type : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-auto">
				{{ Form::select('leave_type_id', \App\Models\HumanResources\OptLeaveType::pluck('leave_type', 'id'), @$value, ['id' => 'leave_id', 'class' => 'form-control col-auto']) }}
			</div>
		</div>

		<div class="form-group row mb-3 {{ $errors->has('reason') ? 'has-error' : '' }}">
			{{ Form::label( 'reason', 'Reason : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-auto">
				{{ Form::textarea('reason', @$value, ['class' => 'form-control col-auto', 'id' => 'reason', 'placeholder' => 'Reason', 'autocomplete' => 'off']) }}
			</div>
		</div>

		<div id="wrapper">
		</div>

		<div class="form-group row mb-3 {{ $errors->has('akuan') ? 'has-error' : '' }}">
			<div class="offset-sm-2 col-auto">
				{{ Form::checkbox('akuan', 1, @$value, ['class' => 'form-check-input ', 'id' => 'akuan1']) }}
					<label for="akuan1" class="form-check-label p-1 bg-warning text-danger rounded"><p>I hereby confirmed that all details and information filled in are <strong>CORRECT</strong> and <strong>CHECKED</strong> before sending.</p></label>
			</div>
		</div>

		<div class="form-group row mb-3">
			<div class="col-auto offset-sm-2">
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
	} else {																			// other than TF
		// console.log('else');
		$('#wrapper').append(

			'<div id="remove">' +
				'<div class="form-group row mb-3 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
					'{{ Form::label('from', 'From : ', ['class' => 'col-sm-2 col-form-label']) }}' +
					'<div class="col-auto datetime" style="position: relative">' +
						'{{ Form::text('date_time_start', Carbon::parse($hrleave->date_time_start)->format('Y-m-d'), ['class' => 'form-control col-auto', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row mb-3 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
					'{{ Form::label('to', 'To : ', ['class' => 'col-sm-2 col-form-label']) }}' +
					'<div class="col-auto datetime" style="position: relative">' +
						'{{ Form::text('date_time_end', Carbon::parse($hrleave->date_time_end)->format('Y-m-d'), ['class' => 'form-control col-auto', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row mb-3 {{ $errors->has('leave_type') ? 'has-error' : '' }}" id="wrapperday">' +

					'{{ Form::label('leave_type', 'Leave Category : ', ['class' => 'col-sm-2 col-form-label removehalfleave']) }}' +
					'<div class="col-auto mb-3 removehalfleave " id="halfleave">' +
						'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="removeleavehalf">' +
							'<input type="radio" name="leave_type" value="1" id="radio1" class="removehalfleave" checked="checked">' +
							'<div class="state p-success removehalfleave">' +
								'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
							'</div>' +
						'</div>' +
						'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="appendleavehalf">' +
							'<input type="radio" name="leave_type" value="2" id="radio2" class="removehalfleave" >' +
							'<div class="state p-success removehalfleave">' +
								'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
							'</div>' +
						'</div>' +
					'</div>' +
					'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +

					'</div>' +

					'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +

					'</div>' +
				'</div>' +
				'@if( $userneedbackup == 1 )' +
				'<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
					'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-2 col-form-label']) }}' +
					'<div class="col-auto backup">' +
						'{{ Form::select('staff_id', \App\Models\Staff::where('active', 1)->pluck('name', 'id'), !is_null($hrleave->hasmanyleaveapprovalbackup()->first()?->staff_id)?$hrleave->hasmanyleaveapprovalbackup()->first()?->staff_id:NULL, ['id' => 'backupperson', 'class' => 'form-control form-select form-select-sm', 'placeholder' => 'Please Choose']) }}' +
					'</div>' +
				'</div>' +
				'@endif' +
				'<div class="form-group row mb-3 {{ $errors->has('document') ? 'has-error' : '' }}">' +
					'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-2 col-form-label'] ) }}' +
					'<div class="col-auto supportdoc">' +
						'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
					'</div>' +
				'</div>' +
				'<div class="form-group row mb-3 {{ $errors->has('documentsupport') ? 'has-error' : '' }}">' +
					'<div class="offset-sm-2 col-auto form-check suppdoc">' +
						'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input ', 'id' => 'suppdoc']) }}' +
						'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</label>' +
					'</div>' +
				'</div>' +
			'</div>'

		);
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
	});

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

					'<div class="form-group row mb-3 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
						'{{ Form::label('from', 'From : ', ['class' => 'col-sm-2 col-form-label']) }}' +
						'<div class="col-auto datetime" style="position: relative">' +
							'{{ Form::text('date_time_start', @$value, ['class' => 'form-control col-auto', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +

					'<div class="form-group row mb-3 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
						'{{ Form::label('to', 'To : ', ['class' => 'col-sm-2 col-form-label']) }}' +
						'<div class="col-auto datetime" style="position: relative">' +
							'{{ Form::text('date_time_end', @$value, ['class' => 'form-control col-auto', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +

					'<div class="form-group row mb-3 {{ $errors->has('leave_type') ? 'has-error' : '' }}" id="wrapperday">' +
						'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>' +
					'</div>' +

					@if( $userneedbackup == 1 )
					'<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-2 col-form-label']) }}' +
						'<div class="col-auto backup">' +
							'<select name="staff_id" id="backupperson" class="form-control form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
					@endif

					'<div class="form-group row mb-3 {{ $errors->has('document') ? 'has-error' : '' }}">' +
						'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-2 col-form-label'] ) }}' +
						'<div class="col-auto supportdoc">' +
							'{{ Form::file( 'document', ['class' => 'form-control form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
						'</div>' +
					'</div>' +

					'<div class="form-group row mb-3 {{ $errors->has('documentsupport') ? 'has-error' : '' }}">' +
						'<div class="offset-sm-2 col-auto form-check suppdoc">' +
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

					'<div class="form-group row mb-3 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
						'{{ Form::label('from', 'From : ', ['class' => 'col-sm-2 col-form-label']) }}' +
						'<div class="col-auto datetime" style="position: relative">' +
							'{{ Form::text('date_time_start', @$value, ['class' => 'form-control col-auto', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +

					'<div class="form-group row mb-3 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
						'{{ Form::label('to', 'To : ', ['class' => 'col-sm-2 col-form-label']) }}' +
						'<div class="col-auto datetime" style="position: relative">' +
							'{{ Form::text('date_time_end', @$value, ['class' => 'form-control col-auto', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +

					'<div class="form-group row mb-3 {{ $errors->has('leave_type') ? 'has-error' : '' }}" id="wrapperday">' +
						'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>' +
					'</div>' +

					@if( $userneedbackup == 1 )
					'<div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-2 col-form-label']) }}' +
						'<div class="col-auto backup">' +
							'<select name="staff_id" id="backupperson" class="form-control form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
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
							'{{ Form::label('leave_type', 'Leave Category : ', ['class' => 'col-sm-2 col-form-label removehalfleave']) }}' +
							'<div class="col-auto mb-3 removehalfleave " id="halfleave">' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_type" value="1" id="radio1" class="removehalfleave" disabled="disabled">' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
								'</div>' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_type" value="2" id="radio2" class="removehalfleave" checked="checked">' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
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
						'<div class="pretty p-default p-curve form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
							'<div class="state p-primary">' +
								'<label for="am" class="form-check-label">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
						'</div>' +
						'<div class="pretty p-default p-curve form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
							'<div class="state p-primary">' +
								'<label for="pm" class="form-check-label">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

} else {
					$('#wrapperday').append(
							'{{ Form::label('leave_type', 'Leave Category : ', ['class' => 'col-sm-2 col-form-label removehalfleave']) }}' +
							'<div class="col-auto mb-3 removehalfleave " id="halfleave">' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_type" value="1" id="radio1" class="removehalfleave" checked="checked">' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
								'</div>' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_type" value="2" id="radio2" class="removehalfleave" >' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +

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
							'{{ Form::label('leave_type', 'Leave Category : ', ['class' => 'col-sm-2 col-form-label removehalfleave']) }}' +
							'<div class="col-auto mb-3 removehalfleave " id="halfleave">' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_type" value="1" id="radio1" class="removehalfleave" disabled="disabled">' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
								'</div>' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_type" value="2" id="radio2" class="removehalfleave" checked="checked">' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
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
						'<div class="pretty p-default p-curve form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" ' + toggle_time_start_am + ' ' + checkedam + '>' +
							'<div class="state p-primary">' +
								'<label for="am" class="form-check-label">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
						'</div>' +
						'<div class="pretty p-default p-curve form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" ' + toggle_time_start_pm + ' ' + checkedpm + '>' +
							'<div class="state p-primary">' +
								'<label for="pm" class="form-check-label">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
						'</div>'
					);
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_start"]'));
					$('#form').bootstrapValidator('addField', $('.time').find('[name="time_end"]'));

} else {
					$('#wrapperday').append(
							'{{ Form::label('leave_type', 'Leave Category : ', ['class' => 'col-sm-2 col-form-label removehalfleave']) }}' +
							'<div class="col-auto mb-3 removehalfleave " id="halfleave">' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="removeleavehalf">' +
									'<input type="radio" name="leave_type" value="1" id="radio1" class="removehalfleave" checked="checked">' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
								'</div>' +
								'<div class="pretty p-default p-curve form-check form-check-inline removehalfleave" id="appendleavehalf">' +
									'<input type="radio" name="leave_type" value="2" id="radio2" class="removehalfleave" >' +
									'<div class="state p-success removehalfleave">' +
										'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label removehalfleave']) }}' +
									'</div>' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-auto offset-sm-2 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +

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
						'<div class="pretty p-default p-curve form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" checked="checked">' +
							'<div class="state p-primary">' +
								'<label for="am" class="form-check-label">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
						'</div>' +
						'<div class="pretty p-default p-curve form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm">' +
							'<div class="state p-primary">' +
								'<label for="pm" class="form-check-label">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
							'</div>' +
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
