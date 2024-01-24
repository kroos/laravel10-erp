<?php

use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HROvertimeRange;
?>
@extends('layouts.app')

@section('content')
<style>
  .scrollable-div {
    /* Set the width height as needed */
    /*		width: 100%;*/
    height: 400px;
    background-color: blanchedalmond;
    /* Add scrollbars when content overflows */
    overflow: auto;
  }

  p {
    margin-top: 4px;
    margin-bottom: 4px;
  }
</style>

<?php
// who am i?
$me1 = \Auth::user()->belongstostaff->div_id == 1;    // hod
$me2 = \Auth::user()->belongstostaff->div_id == 5;    // hod assistant
$me3 = \Auth::user()->belongstostaff->div_id == 4;    // supervisor
$me4 = \Auth::user()->belongstostaff->div_id == 3;    // HR
$me5 = \Auth::user()->belongstostaff->authorise_id == 1;  // admin
$me6 = \Auth::user()->belongstostaff->div_id == 2;    // director
$dept = \Auth::user()->belongstostaff->belongstomanydepartment()->wherePivot('main', 1)->first();
$deptid = $dept->id;
$branch = $dept->branch_id;
$category = $dept->category_id;

$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->where(function ($query) {
    $query->where('staffs.div_id', '!=', 2)
      ->orWhereNull('staffs.div_id');
  })
  ->select('staffs.id as staffID', 'staffs.*', 'logins.*')
  ->orderBy('logins.username', 'asc')
  ->get();
?>

<div class="col-sm-12 row">
  @include('humanresources.hrdept.navhr')

  <h4 class="align-items-start">Add Overtime Staff</h4>

  {{ Form::open(['route' => ['overtime.store'], 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}

  <div class="form-group row mb-3 {{ $errors->has('staff_id') ? 'has-error' : '' }}">
    {{ Form::label( 'rel', 'Staff : ', ['class' => 'col-sm-2 col-form-label'] ) }}
    <div class="col-md-10">
      <div class="scrollable-div">

        @if($staffs->count())
        @foreach($staffs as $k)
        <?php
        if ($me1) {                                        // hod
          if ($deptid == 21) {                                // hod | dept prod A
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
          } elseif ($deptid == 28) {                              // hod | not dept prod A | dept prod B
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2;
          } elseif ($deptid == 14) {                              // hod | not dept prod A | not dept prod B | HR
            $ha = true;
          } elseif ($deptid == 6) {                              // hod | not dept prod A | not dept prod B | not HR | cust serv
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
          } elseif ($deptid == 23) {                              // hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 16 || $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 17;
          } else {                                      // hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid;
          }
        } elseif ($me2) {                                    // not hod | asst hod
          if ($deptid == 14) {                                  // not hod | not dept prod A | not dept prod B | HR
            $ha = true;
          } elseif ($deptid == 6) {                              // not hod | not dept prod A | not dept prod B | not HR | cust serv
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == 7;
          }
        } elseif ($me3) {                                    // not hod | not asst hod | supervisor
          if ($branch == 1) {                                  // not hod | not asst hod | supervisor | branch A
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || ($k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
          } elseif ($branch == 2) {                              // not hod | not asst hod | supervisor | not branch A | branch B
            $ha = $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->id == $deptid || ($k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->category_id == 2 && $k->belongstomanydepartment()?->wherePivot('main', 1)->first()?->branch_id == $branch);
          }
        } elseif ($me6) {                                    // not hod | not asst hod | not supervisor | director
          $ha = true;
        } elseif ($me5) {                                    // not hod | not asst hod | not supervisor | not director | admin
          $ha = true;
        } else {
          $ha = false;
        }
        ?>
        @if( $ha )
        <div class="form-check mb-1 g-3">
          <input class="form-check-input" name="staff_id[]" type="checkbox" value="{{ $k->staffID }}" id="staff_{{ $k->staffID }}">
          <label class="form-check-label" for="staff_{{ $k->staffID }}">{{ $k->username }} - {{ $k->name }}</label>
        </div>
        @endif
        @endforeach
        @endif
      </div>
    </div>
  </div>

  <div class="form-group row mb-3 {{ $errors->has('overtime_range_id') ? 'has-error' : '' }}">
    {{ Form::label( 'mar', 'Overtime : ', ['class' => 'col-sm-2 col-form-label'] ) }}
    <div class="col-sm-10">
      <select name="overtime_range_id" id="mar" class="form-select form-select-sm col-sm-8" placeholder="Please Select"></select>
    </div>
  </div>

  <div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
    {{ Form::label( 'nam', 'Date Overtime : ', ['class' => 'col-sm-2 col-form-label'] ) }}
    <div class="col-md-10" style="position: relative">
      {{ Form::text('ot_date', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'nam', 'placeholder' => 'Date Overtime', 'autocomplete' => 'off']) }}
    </div>
  </div>

  <div class="form-group row mb-3 {{ $errors->has('ot_date') ? 'has-error' : '' }}">
    {{ Form::label( 'rem', 'Remarks : ', ['class' => 'col-sm-2 col-form-label'] ) }}
    <div class="col-sm-10">
      {{ Form::textarea('remark', @$value, ['class' => 'form-control form-control-sm col-auto', 'id' => 'rem', 'placeholder' => 'Remarks', 'autocomplete' => 'off', 'cols' => '120', 'rows' => '3']) }}
    </div>
  </div>

  <div class="form-group row mb-3 g-3 p-2">
    <div class="col-sm-10 offset-sm-2">
      {!! Form::submit('Add Overtime Staff', ['class' => 'btn btn-sm btn-outline-secondary']) !!}
    </div>
  </div>

  {{ Form::close() }}
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
$('#rel').select2({
	placeholder: 'Please Select',
	width: '100%',
	ajax: {
		url: '{{ route('samelocationstaff') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				id: {{ \Auth::user()->belongstostaff->id }},
				_token: '{!! csrf_token() !!}',
				search: params.term,
			}
			return query;
		}
	},
	allowClear: true,
	closeOnSelect: true,
});

$('#mar').select2({
	placeholder: 'Please Select',
	width: '100%',
	ajax: {
		url: '{{ route('overtimerange') }}',
		type: 'POST',
		dataType: 'json',
		data: function (params) {
			var query = {
				id: {{ \Auth::user()->belongstostaff->id }},
				_token: '{!! csrf_token() !!}',
				search: params.term,
			}
			return query;
		}
	},
	allowClear: true,
	closeOnSelect: true,
});

/////////////////////////////////////////////////////////////////////////////////////////
<?php
$mi = \Auth::user()->belongstostaff;

// position
$pos = $mi->div_id;

// dept HR
$dept = $mi->belongstomanydepartment()->where('main', 1)->first();
?>

$('#nam').datetimepicker({
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
	format: 'YYYY-MM-DD',
	useCurrent: true,
	@if( $pos == 4 || ($pos == 1 && $dept->department_id == 21) )
		minDate: moment().format(),
	@endif
}).on("dp.change", function (e) {
	$('#form').bootstrapValidator('revalidateField', 'ot_date');
});

/////////////////////////////////////////////////////////////////////////////////////////
// bootstrap validator
$('#form').bootstrapValidator({
	feedbackIcons: {
		valid: '',
		invalid: '',
		validating: ''
	},
	fields: {
		ot_date: {
			validators: {
				notEmpty: {
					message: 'Please insert date. '
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'The value is not a valid date ',
				},
			}
		},
		staff_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
		overtime_range_id: {
			validators: {
				notEmpty: {
					message: 'Please choose. '
				},
			}
		},
	}
});

@endsection
