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
$spouses = $profile->hasmanyspouse()->get();
$childrens = $profile->hasmanychildren()->get();
$totalRows = $emergencies->count()
?>

<div class="container rounded bg-white mt-2 mb-2">

  {!! Form::model($profile, ['route' => ['profile.update', $profile->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}

  <div class="row">
    <div class="col-md-2 border-right">
      <div class="d-flex flex-column align-items-center text-center p-3 py-5">
        <img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
        <span class="font-weight-bold">ID: {{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</span>
        <span> </span>
      </div>
    </div>
    <div class="col-md-10 border-right">
      <div class="p-1 py-3">
        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Staff Profile</h4>
          </div>
        </div>
        <div class="row mb-5">
          <div class="col-md-6 border-right">
            <div class="px-3">
              <div class="row mt-3">
                <div class="col-md-12">
                  <label for="name" class="labels">Name</label>
                  <input type="text" class="form-control" value="{{ $profile->name }}" readonly>
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
            </div>
          </div>
          <div class="col-md-6 border-right">
            <div class="px-3">
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
        </div>


        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Emergency Contact</h4>
          </div>
        </div>
        <div class="row mb-5">
          <div class="col-md-6 border-right">
            <div class="px-3">

              @foreach ($emergencies as $emergency)
              @if ($loop->odd)

              <div>
                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $emergency->contact_person }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">RELATIONSHIP</label>
                    <input type="text" class="form-control" value="{{ $emergency->belongstorelationship->relationship}}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">PHONE NUMBER</label>
                    <input type="text" class="form-control" value="{{ $emergency->phone }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">ADDRESS</label>
                    <input type="text" class="form-control" value="{{ $emergency->address }}" readonly>
                  </div>
                </div>
              </div>

              @endif
              @endforeach

            </div>
          </div>
          <div class="col-md-6 border-right">
            <div class="px-3">

              @foreach ($emergencies as $emergency)
              @if ($loop->even)

              <div>
                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $emergency->contact_person }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">RELATIONSHIP</label>
                    <input type="text" class="form-control" value="{{ $emergency->belongstorelationship->relationship }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">PHONE NUMBER</label>
                    <input type="text" class="form-control" value="{{ $emergency->phone }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">ADDRESS</label>
                    <input type="text" class="form-control" value="{{ $emergency->address }}" readonly>
                  </div>
                </div>
              </div>

              @endif
              @endforeach

            </div>
          </div>
        </div>


        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Spouse</h4>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 border-right">
            <div class="px-3">

              @foreach ($spouses as $spouse)
              @if ($loop->odd)

              <div class="mb-5">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $spouse->spouse }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">IC</label>
                    <input type="text" class="form-control" value="{{ $spouse->id_card_passport }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">PHONE NUMBER</label>
                    <input type="text" class="form-control" value="{{ $spouse->phone }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">Date Of Birth</label>
                    <input type="text" class="form-control" value="{{ $spouse->dob }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">Profession</label>
                    <input type="text" class="form-control" value="{{ $spouse->profession }}" readonly>
                  </div>
                </div>
              </div>

              @endif
              @endforeach

            </div>
          </div>
          <div class="col-md-6 border-right">
            <div class="px-3">

              @foreach ($spouses as $spouse)
              @if ($loop->even)

              <div class="mb-5">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $spouse->spouse }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">IC</label>
                    <input type="text" class="form-control" value="{{ $spouse->id_card_passport }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">PHONE NUMBER</label>
                    <input type="text" class="form-control" value="{{ $spouse->phone }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">Date Of Birth</label>
                    <input type="text" class="form-control" value="{{ $spouse->dob }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">Profession</label>
                    <input type="text" class="form-control" value="{{ $spouse->profession }}" readonly>
                  </div>
                </div>
              </div>

              @endif
              @endforeach

            </div>
          </div>
        </div>


        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Children</h4>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 border-right">
            <div class="px-3">

              @foreach ($childrens as $children)
              @if ($loop->odd)

              <div class="mb-5">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $children->children }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">Date Of Birth</label>
                    <input type="text" class="form-control" value="{{ $children->dob }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">Gender</label>
                    <input type="text" class="form-control" value="{{ $children->belongstogender->gender }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">Health Condition</label>
                    <input type="text" class="form-control" value="{{ $children->belongstohealthstatus->health_status }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">Education Level</label>
                    <input type="text" class="form-control" value="{{ $children->belongstoeducationlevel->education_level }}" readonly>
                  </div>
                </div>
              </div>

              @endif
              @endforeach

            </div>
          </div>
          <div class="col-md-6 border-right">
            <div class="px-3">

              @foreach ($childrens as $children)
              @if ($loop->even)

              <div class="mb-5">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $children->children }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <label class="labels">Date Of Birth</label>
                    <input type="text" class="form-control" value="{{ $children->dob }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="labels">Gender</label>
                    <input type="text" class="form-control" value="{{ $children->belongstogender->gender }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">Health Condition</label>
                    <input type="text" class="form-control" value="{{ $children->belongstohealthstatus->health_status }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12">
                    <label class="labels">Education Level</label>
                    <input type="text" class="form-control" value="{{ $children->belongstoeducationlevel->education_level }}" readonly>
                  </div>
                </div>
              </div>

              @endif
              @endforeach

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {!! Form::close() !!}

</div>

@endsection

@section('js')

@endsection