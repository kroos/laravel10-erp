@extends ('layouts.app')

@section('content')
<style>
  .btn-sm-custom {
    padding: 0px;
    border-radius: 8px;
    height: 25px;
    width: 40px;
  }
</style>

<?php
$gender = App\Models\HumanResources\OptGender::all()->pluck('gender', 'id')->sortKeys()->toArray();
$nationality = App\Models\HumanResources\OptCountry::all()->pluck('country', 'id')->sortKeys()->toArray();
$religion = App\Models\HumanResources\OptReligion::all()->pluck('religion', 'id')->sortKeys()->toArray();
$race = App\Models\HumanResources\OptRace::all()->pluck('race', 'id')->sortKeys()->toArray();
$marital_status = App\Models\HumanResources\OptMaritalStatus::all()->pluck('marital_status', 'id')->sortKeys()->toArray();
$relationship = App\Models\HumanResources\OptRelationship::all()->pluck('relationship', 'id')->sortKeys()->toArray();
$emergencies = $profile->hasmanyemergency()->get();
$totalRows = $emergencies->count()
?>

<div class="container rounded bg-white mt-2 mb-2">

  {!! Form::model($profile, ['route' => ['profile.update', $profile->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}

  <div class="row">
    <div class="col-md-3 border-right">
      <div class="d-flex flex-column align-items-center text-center p-3 py-5">
        <img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
        <span class="font-weight-bold">{{ $profile-> name}}</span>
        <span class="font-weight-bold">{{ $profile-> hasmanylogin() -> where('active', 1) -> first() -> username}}</span>
        <span> </span>
      </div>
    </div>
    <div class="col-md-5 border-right">
      <div class="p-3 py-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="text-right">Profile Update</h4>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label for="name" class="labels">NAME</label>
            <input type="text" id="name" class="form-control" value="{{ $profile->name }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6 {{ $errors->has('ic') ? 'has-error' : '' }}">
            <label for="ic" class="labels">IC</label>
            {!! Form::text( 'ic', @$value, ['class' => 'form-control', 'id' => 'ic', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6 {{ $errors->has('mobile') ? 'has-error' : '' }}">
            <label for="mobile" class="labels">PHONE NUMBER</label>
            {!! Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12 {{ $errors->has('email') ? 'has-error' : '' }}">
            <label for="email" class="labels">EMAIL</label>
            {!! Form::text( 'email', @$value, ['class' => 'form-control', 'id' => 'email', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12 {{ $errors->has('address') ? 'has-error' : '' }}">
            <label for="address" class="labels">ADDRESS</label>
            {!! Form::text( 'address', @$value, ['class' => 'form-control', 'id' => 'address', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label for="department" class="labels">DEPARTMENT</label>
            <input type="text" id="department" class="form-control" value="{{ $profile->belongstomanydepartment()->first()->department }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label for="category" class="labels">CATEGORY</label>
            <input type="text" id="category" class="form-control" value="{{ $profile->belongstomanydepartment->first()->belongstocategory->category }}" readonly>
          </div>
          <div class="col-md-6">
            <label for="restday_group_id" class="labels">SATURDAY GROUPING</label>
            <input type="text" id="restday_group_id" class="form-control" value="Group {{ $profile->restday_group_id }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6 {{ $errors->has('dob') ? 'has-error' : '' }}">
            <label for="dob" class="labels">DATE OF BIRTH</label>
            {!! Form::text( 'dob', @$value, ['class' => 'form-control', 'id' => 'dob', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6 {{ $errors->has('gender_id') ? 'has-error' : '' }}">
            <label for="gender_id" class="labels">GENDER</label>
            {!! Form::select( 'gender_id', $gender, @$value, ['class' => 'form-control', 'id' => 'gender_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6 {{ $errors->has('nationality_id') ? 'has-error' : '' }}">
            <label for="nationality_id" class="labels">NATIONALITY</label>
            {!! Form::select( 'nationality_id', $nationality, @$value, ['class' => 'form-control', 'id' => 'nationality_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6 {{ $errors->has('race_id') ? 'has-error' : '' }}">
            <label for="race_id" class="labels">RACE</label>
            {!! Form::select( 'race_id', $race, @$value, ['class' => 'form-control', 'id' => 'race_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6 {{ $errors->has('religion_id') ? 'has-error' : '' }}">
            <label for="religion_id" class="labels">RELIGION</label>
            {!! Form::select( 'religion_id', $religion, @$value, ['class' => 'form-control', 'id' => 'religion_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6 {{ $errors->has('marital_status_id') ? 'has-error' : '' }}">
            <label for="marital_status_id" class="labels">MARITAL STATUS</label>
            {!! Form::select( 'marital_status_id', $marital_status, @$value, ['class' => 'form-control', 'id' => 'marital_status_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label for="join_date" class="labels">JOIN DATE</label>
            <input type="text" id="join_date" class="form-control" value="{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}" readonly>
          </div>
          <div class="col-md-6">
            <label for="confirm_date" class="labels">CONFIRM DATE</label>
            <input type="text" id="confirm_date" class="form-control" value="{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}" readonly>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 border-right">
      <div class="p-3 py-5 wrap_emergency">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="text-right">Emergency Contact</h4>
          @if ($totalRows < 3) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_emergency" type="button">
            <i class="bi-plus" aria-hidden="true"></i>&nbsp;Contact
            </button>
            @endif
        </div>

        <?php $i = 1; ?>
        @if ($emergencies->isNotEmpty())
        @foreach ($emergencies as $emergency)

        <div class="table_emergency">
          <input type="hidden" name="emer[{{ $i }}][id]" value="{{ $emergency->id }}">
          <input type="hidden" name="emer[{{ $i }}][staff_id]" value="{{ $profile-> id }}">

          <div class="row mt-3">
            <div class="col-md-12 {{ $errors->has('emer.'.$i.'.contact_person') ? 'has-error' : '' }}">
              <label for="contact_person" class="labels">
                NAME
              </label>
              {!! Form::text( "emer[$i][contact_person]", @$emergency->contact_person, ['class' => 'form-control', 'id' => 'contact_person', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-6 {{ $errors->has('emer.'.$i.'.relationship_id') ? 'has-error' : '' }}">
              <label for="relationship_id" class="labels">RELATIONSHIP</label>
              {!! Form::select( "emer[$i][relationship_id]", $relationship, @$emergency->relationship_id, ['class' => 'form-control', 'id' => 'relationship_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
            </div>
            <div class="col-md-6 {{ $errors->has('emer.'.$i.'.phone') ? 'has-error' : '' }}">
              <label for="phone" class="labels">PHONE NUMBER</label>
              {!! Form::text( "emer[$i][phone]", @$emergency->phone, ['class' => 'form-control', 'id' => 'phone', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12 {{ $errors->has('emer.'.$i.'.address') ? 'has-error' : '' }}">
              <label for="emergency_address" class="labels">ADDRESS</label>
              {!! Form::text( "emer[$i][address]", @$emergency->address, ['class' => 'form-control', 'id' => 'emergency_address', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
            </div>
          </div>

          <div class="mt-1 d-flex flex-row justify-content-end">
            <button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg delete_emergency" data-id="{{ $emergency->id }}"></button>
          </div>

          <?php $i++; ?>
        </div>
        @endforeach
        @endif

      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-9 container">
      <div class="text-center">
        {!! Form::button('Save', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
      </div>
    </div>
  </div>

  {!! Form::close() !!}

  <div class="row mt-3">
    <div class="col-md-3"></div>
    <div class="col-md-9">
      <div class="text-center">
        <a href="{{ url()->previous() }}">
          <button class="btn btn-sm btn-outline-secondary">Back</button>
        </a>
      </div>
    </div>
  </div>

</div>

@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
// DELETE EMERGENCY
$(document).on('click', '.delete_emergency', function(e){
	var ackID = $(this).data('id');
	SwalDelete(ackID);
	e.preventDefault();
});

function SwalDelete(ackID){
	swal.fire({
		title: 'Delete Emergency Contact',
		text: 'Are you sure to delete this contact?',
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
					url: '{{ url('profile') }}' + '/' + ackID,
					type: 'DELETE',
					dataType: 'json',
					data: {
							id: ackID,
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
// ADD EMERGENCY
var max_emergency = 3;
var totalRows = {{ $totalRows }};

$(".add_emergency").click(function() {

if(totalRows < max_emergency) { totalRows++; $(".wrap_emergency").append( '<div class="table_emergency">' + '<input type="hidden" name="emer[' + totalRows +'][id]" value="">' +
  '<input type="hidden" name="emer['+ totalRows +'][staff_id]" value="{{ $profile-> id}}">' +

  '<div class="row mt-3">' +
    '<div class="col-md-12 {{ $errors->has('emer.*.contact_person') ? 'has-error' : '' }}">' +
      '<label for="contact_person" class="labels">NAME</label>' +
      '{!! Form::text( "emer[$i][contact_person]", @$value, ['class' => 'form-control', 'id' => 'contact_person', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
      '</div>' +
    '</div>' +

  '<div class="row mt-3">' +
    '<div class="col-md-6 {{ $errors->has('emer.*.relationship_id') ? 'has-error' : '' }}">' +
      '<label for="relationship_id" class="labels">RELATIONSHIP</label>' +
      '{!! Form::select( "emer[$i][relationship_id]", $relationship, @$value, ['class' => 'form-control', 'id' => 'relationship_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}' +
      '</div>' +
    '<div class="col-md-6 {{ $errors->has('emer.*.phone') ? 'has-error' : '' }}">' +
      '<label for="phone" class="labels">PHONE NUMBER</label>' +
      '{!! Form::text( "emer[$i][phone]", @$value, ['class' => 'form-control', 'id' => 'phone', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
      '</div>' +
    '</div>' +

  '<div class="row mt-3">' +
    '<div class="col-md-12 {{ $errors->has('emer.*.address') ? 'has-error' : '' }}">' +
      '<label for="emergency_address" class="labels">ADDRESS</label>' +
      '{!! Form::text( "emer[$i][address]", @$value, ['class' => 'form-control', 'id' => 'emergency_address', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
      '</div>' +
    '</div>' +

  '<div class="mt-1 d-flex flex-row justify-content-end">' +
    '<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg remove_emergency"></button>' +
    '</div>' +
  '</div>'

  );

  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows +'][id]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows +'][staff_id]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows +'][contact_person]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows +'][relationship_id]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows +'][phone]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows +'][address]"]'));
  }
  })

  $(".wrap_emergency").on("click",".remove_emergency", function(e){
  e.preventDefault();
  var $row = $(this).parent().parent();
  var $option1 = $row.find('[name="emer['+ totalRows +'][id]"]');
  var $option2 = $row.find('[name="emer['+ totalRows +'][staff_id]"]');
  var $option3 = $row.find('[name="emer['+ totalRows +'][contact_person]"]');
  var $option4 = $row.find('[name="emer['+ totalRows +'][relationship_id]"]');
  var $option5 = $row.find('[name="emer['+ totalRows +'][phone]"]');
  var $option6 = $row.find('[name="emer['+ totalRows +'][address]"]');
  $row.remove();

  $('#form').bootstrapValidator('removeField', $option1);
  $('#form').bootstrapValidator('removeField', $option2);
  $('#form').bootstrapValidator('removeField', $option3);
  $('#form').bootstrapValidator('removeField', $option4);
  $('#form').bootstrapValidator('removeField', $option5);
  $('#form').bootstrapValidator('removeField', $option6);
  console.log();
  totalRows--;
  })

  /////////////////////////////////////////////////////////////////////////////////////////
  // DATE PICKER
  $('#dob').datetimepicker({
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
  useCurrent: false,
  });

  /////////////////////////////////////////////////////////////////////////////////////////
  // SELECTION
  $('#nationality_id').select2({
  placeholder: 'Please Select',
  width: '100%',
  allowClear: true,
  closeOnSelect: true,
  });

  $('#race_id').select2({
  placeholder: 'Please Select',
  width: '100%',
  allowClear: true,
  closeOnSelect: true,
  });

  /////////////////////////////////////////////////////////////////////////////////////////
  // VALIDATOR
  $(document).ready(function() {
  $('#form').bootstrapValidator({
  feedbackIcons: {
  valid: '',
  invalid: '',
  validating: ''
  },
  fields: {
  ic: {
  validators: {
  notEmpty: {
  message: 'Please insert ic.'
  },
  numeric: {
  message: 'The value is not an numeric'
  }
  }
  },

  mobile: {
  validators: {
  notEmpty: {
  message: 'Please insert mobile number.'
  },
  numeric: {
  message: 'The value is not an numeric'
  }
  }
  },

  email: {
  validators: {
  notEmpty: {
  message: 'Please insert email.'
  },
  emailAddress: {
  message: 'The value is not a valid email.'
  }
  }
  },

  address: {
  validators: {
  notEmpty: {
  message: 'Please insert address.'
  }
  }
  },

  dob: {
  validators: {
  notEmpty: {
  message: 'Please insert date of birth.'
  }
  }
  },

  gender_id: {
  validators: {
  notEmpty: {
  message: 'Please select a gender.'
  }
  }
  },

  nationality_id: {
  validators: {
  notEmpty: {
  message: 'Please select a nationality.'
  }
  }
  },

  race_id: {
  validators: {
  notEmpty: {
  message: 'Please select a race.'
  }
  }
  },

  religion_id: {
  validators: {
  notEmpty: {
  message: 'Please select a religion.'
  }
  }
  },

  marital_status_id: {
  validators: {
  notEmpty: {
  message: 'Please select a marital status.'
  }
  }
  },

  <?php $k = 1; ?>
  <?php foreach ($emergencies as $emergency) { ?>
    'emer[{{ $k }}][contact_person]': {
    validators: {
    notEmpty: {
    message: 'Please insert contact person.'
    }
    }
    },

    'emer[{{ $k }}][relationship_id]': {
    validators: {
    notEmpty: {
    message: 'Please select a relationship.'
    }
    }
    },

    'emer[{{ $k }}][phone]': {
    validators: {
    notEmpty: {
    message: 'Please insert phone number.'
    },
    numeric: {
    message: 'The value is not an numeric'
    }
    }
    },

    'emer[{{ $k }}][address]': {
    validators: {
    notEmpty: {
    message: 'Please insert address.'
    }
    }
    },
    <?php $k++; ?>
  <?php } ?>

  }
  })
  });
  @endsection