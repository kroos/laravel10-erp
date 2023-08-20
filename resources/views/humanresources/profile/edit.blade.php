@extends ('layouts.app')

@section('content')
<style>
  .btn-sm-custom {
    padding: 0px;
    border-radius: 8px;
    height: 25px;
    width: 40px;
  }

  /* div {
    border: 1px solid black;
  } */
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
$totalRows_emergency = $emergencies->count();
$totalRows_spouse = $spouses->count();
$totalRows_children = $childrens->count();
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


        <?php $i = 1 ?>
        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Emergency Contact</h4>
            @if ($totalRows_emergency < 2) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_emergency" type="button">
              <i class="bi-plus" aria-hidden="true"></i>
              </button>
              @endif
          </div>
        </div>
        <div class="row mb-5">
          <div class="col-md-6 border-right">
            <div class="px-3 wrap_emergency_odd">

              @foreach ($emergencies as $emergency)
              @if ($loop->odd)

              <div class="table_emergency">
                <input type="hidden" name="emer[{{ $i }}][id]" value="{{ $emergency->id }}">
                <input type="hidden" name="emer[{{ $i }}][staff_id]" value="{{ $profile-> id }}">

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('emer.'.$i.'.contact_person') ? 'has-error' : '' }}">
                    <label for="contact_person" class="labels">NAME</label>
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
              </div>

              <?php $i++ ?>
              @endif
              @endforeach

            </div>
          </div>
          <div class="col-md-6 border-right">
            <div class="px-3 wrap_emergency_even">

              @foreach ($emergencies as $emergency)
              @if ($loop->even)

              <div class="table_emergency">
                <input type="hidden" name="emer[{{ $i }}][id]" value="{{ $emergency->id }}">
                <input type="hidden" name="emer[{{ $i }}][staff_id]" value="{{ $profile-> id }}">

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('emer.'.$i.'.contact_person') ? 'has-error' : '' }}">
                    <label for="contact_person" class="labels">NAME</label>
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
              </div>

              <?php $i++ ?>
              @endif
              @endforeach

            </div>
          </div>
        </div>




































        <?php $j = 1 ?>
        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Spouse</h4>
            @if ($totalRows_spouse < 4) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_spouse" type="button">
              <i class="bi-plus" aria-hidden="true"></i>
              </button>
              @endif
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 border-right">
            <div class="px-3 wrap_spouse_odd">

              @foreach ($spouses as $spouse)
              @if ($loop->odd)

              <div class="mb-5 table_spouse">
                <input type="hidden" name="spou[{{ $j }}][id]" value="{{ $spouse->id }}">
                <input type="hidden" name="spou[{{ $j }}][staff_id]" value="{{ $profile-> id }}">

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('spou.'.$j.'.spouse') ? 'has-error' : '' }}">
                    <label for="spouse" class="labels">NAME</label>
                    {!! Form::text( "spou[$j][spouse]", @$spouse->spouse, ['class' => 'form-control', 'id' => 'spouse', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.id_card_passport') ? 'has-error' : '' }}">
                    <label for="id_card_passport" class="labels">IC</label>
                    {!! Form::text( "spou[$j][id_card_passport]", @$spouse->id_card_passport, ['class' => 'form-control', 'id' => 'id_card_passport', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.phone') ? 'has-error' : '' }}">
                    <label for="phone" class="labels">PHONE NUMBER</label>
                    {!! Form::text( "spou[$j][phone]", @$spouse->phone, ['class' => 'form-control', 'id' => 'phone', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.dob') ? 'has-error' : '' }}">
                    <label for="dob" class="labels">Date Of Birth</label>
                    {!! Form::text( "spou[$j][dob]", @$spouse->dob, ['class' => 'form-control', 'id' => 'dob', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.profession') ? 'has-error' : '' }}">
                    <label for="profession" class="labels">Profession</label>
                    {!! Form::text( "spou[$j][profession]", @$spouse->profession, ['class' => 'form-control', 'id' => 'profession', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                </div>
              </div>

              <?php $j++ ?>
              @endif
              @endforeach

            </div>
          </div>
          <div class="col-md-6 border-right">
            <div class="px-3 wrap_spouse_even">

              @foreach ($spouses as $spouse)
              @if ($loop->even)

              <div class="mb-5 table_spouse">
                <input type="hidden" name="spou[{{ $j }}][id]" value="{{ $spouse->id }}">
                <input type="hidden" name="spou[{{ $j }}][staff_id]" value="{{ $profile-> id }}">

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('spou.'.$j.'.spouse') ? 'has-error' : '' }}">
                    <label for="spouse" class="labels">NAME</label>
                    {!! Form::text( "spou[$j][spouse]", @$spouse->spouse, ['class' => 'form-control', 'id' => 'spouse', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.id_card_passport') ? 'has-error' : '' }}">
                    <label for="id_card_passport" class="labels">IC</label>
                    {!! Form::text( "spou[$j][id_card_passport]", @$spouse->id_card_passport, ['class' => 'form-control', 'id' => 'id_card_passport', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.phone') ? 'has-error' : '' }}">
                    <label for="phone" class="labels">PHONE NUMBER</label>
                    {!! Form::text( "spou[$j][phone]", @$spouse->phone, ['class' => 'form-control', 'id' => 'phone', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.dob') ? 'has-error' : '' }}">
                    <label for="dob" class="labels">Date Of Birth</label>
                    {!! Form::text( "spou[$j][dob]", @$spouse->dob, ['class' => 'form-control', 'id' => 'dob', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                  <div class="col-md-6 {{ $errors->has('spou.'.$j.'.profession') ? 'has-error' : '' }}">
                    <label for="profession" class="labels">Profession</label>
                    {!! Form::text( "spou[$j][profession]", @$spouse->profession, ['class' => 'form-control', 'id' => 'profession', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}
                  </div>
                </div>
              </div>

              <?php $j++ ?>
              @endif
              @endforeach

            </div>
          </div>
        </div>







































        <?php $k = 1 ?>
        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Children</h4>
            @if ($totalRows_children < 25) <button class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary add_children" type="button">
              <i class="bi-plus" aria-hidden="true"></i>
              </button>
              @endif
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 border-right">
            <div class="px-3 wrap_children_odd">

              @foreach ($childrens as $children)
              @if ($loop->odd)

              <div class="mb-5 table_children">
                <input type="hidden" name="chil[{{ $k }}][id]" value="{{ $children->id }}">
                <input type="hidden" name="chil[{{ $k }}][staff_id]" value="{{ $profile-> id }}">

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('chil.'.$k.'.spouse') ? 'has-error' : '' }}">
                    <label for="children" class="labels">NAME</label>
                    <input type="text" class="form-control" value="{{ $children->children }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6 {{ $errors->has('chil.'.$k.'.spouse') ? 'has-error' : '' }}">
                    <label for="dob" class="labels">Date Of Birth</label>
                    <input type="text" class="form-control" value="{{ $children->dob }}" readonly>
                  </div>
                  <div class="col-md-6 {{ $errors->has('chil.'.$k.'.spouse') ? 'has-error' : '' }}">
                    <label for="" class="labels">Gender</label>
                    <input type="text" class="form-control" value="{{ $children->belongstogender->gender }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('chil.'.$k.'.spouse') ? 'has-error' : '' }}">
                    <label for="" class="labels">Health Condition</label>
                    <input type="text" class="form-control" value="{{ $children->belongstohealthstatus->health_status }}" readonly>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-12 {{ $errors->has('chil.'.$k.'.spouse') ? 'has-error' : '' }}">
                    <label for="" class="labels">Education Level</label>
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
/////////////////////////////////////////////////////////////////////////////////////////
// ADD EMERGENCY
var max_emergency = 2;
var totalRows_emergency = {{ $totalRows_emergency }};

$(".add_emergency").click(function() {

if (totalRows_emergency % 2 === 0) {
var wrap_emergency = $(".wrap_emergency_odd");
} else {
var wrap_emergency = $(".wrap_emergency_even");
}

if(totalRows_emergency < max_emergency) { totalRows_emergency++; wrap_emergency.append( '<div class="table_emergency">' + '<input type="hidden" name="emer[' + totalRows_emergency +'][id]" value="">' +
  '<input type="hidden" name="emer['+ totalRows_emergency +'][staff_id]" value="{{ $profile-> id}}">' +

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

  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][id]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][staff_id]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][contact_person]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][relationship_id]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][phone]"]'));
  $('#form').bootstrapValidator('addField', $('.table_emergency') .find('[name="emer['+ totalRows_emergency +'][address]"]'));
  }
  })

  // DELETE EMERGENCY
  $(".wrap_emergency_odd").on("click",".remove_emergency", function(e){
  e.preventDefault();
  var $row = $(this).parent().parent();
  var $option1 = $row.find('[name="emer['+ totalRows_emergency +'][id]"]');
  var $option2 = $row.find('[name="emer['+ totalRows_emergency +'][staff_id]"]');
  var $option3 = $row.find('[name="emer['+ totalRows_emergency +'][contact_person]"]');
  var $option4 = $row.find('[name="emer['+ totalRows_emergency +'][relationship_id]"]');
  var $option5 = $row.find('[name="emer['+ totalRows_emergency +'][phone]"]');
  var $option6 = $row.find('[name="emer['+ totalRows_emergency +'][address]"]');
  $row.remove();

  $('#form').bootstrapValidator('removeField', $option1);
  $('#form').bootstrapValidator('removeField', $option2);
  $('#form').bootstrapValidator('removeField', $option3);
  $('#form').bootstrapValidator('removeField', $option4);
  $('#form').bootstrapValidator('removeField', $option5);
  $('#form').bootstrapValidator('removeField', $option6);
  console.log();
  totalRows_emergency--;
  })

  // DELETE EMERGENCY
  $(".wrap_emergency_even").on("click",".remove_emergency", function(e){
  e.preventDefault();
  var $row = $(this).parent().parent();
  var $option1 = $row.find('[name="emer['+ totalRows_emergency +'][id]"]');
  var $option2 = $row.find('[name="emer['+ totalRows_emergency +'][staff_id]"]');
  var $option3 = $row.find('[name="emer['+ totalRows_emergency +'][contact_person]"]');
  var $option4 = $row.find('[name="emer['+ totalRows_emergency +'][relationship_id]"]');
  var $option5 = $row.find('[name="emer['+ totalRows_emergency +'][phone]"]');
  var $option6 = $row.find('[name="emer['+ totalRows_emergency +'][address]"]');
  $row.remove();

  $('#form').bootstrapValidator('removeField', $option1);
  $('#form').bootstrapValidator('removeField', $option2);
  $('#form').bootstrapValidator('removeField', $option3);
  $('#form').bootstrapValidator('removeField', $option4);
  $('#form').bootstrapValidator('removeField', $option5);
  $('#form').bootstrapValidator('removeField', $option6);
  console.log();
  totalRows_emergency--;
  })


  /////////////////////////////////////////////////////////////////////////////////////////
  // ADD SPOUSE
  var max_spouse = 4;
  var totalRows_spouse = {{ $totalRows_spouse }};

  $(".add_spouse").click(function() {

  if (totalRows_spouse % 2 === 0) {
  var wrap_spouse = $(".wrap_spouse_odd");
  } else {
  var wrap_spouse = $(".wrap_spouse_even");
  }

  if(totalRows_spouse < max_spouse) { totalRows_spouse++; wrap_spouse.append( '<div class="mb-5 table_spouse">' + '<input type="hidden" name="spou[' + totalRows_spouse +'][id]" value="">' +
    '<input type="hidden" name="spou['+ totalRows_spouse +'][staff_id]" value="{{ $profile-> id }}">' +

    '<div class="row mt-3">' +
      '<div class="col-md-12 {{ $errors->has('spou.*.spouse') ? 'has-error' : '' }}">' +
        '<label for="spouse" class="labels">NAME</label>' +
        '{!! Form::text( "spou[$j][spouse]", @$value, ['class' => 'form-control', 'id' => 'spouse', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
        '</div>' +
      '</div>' +

    '<div class="row mt-3">' +
      '<div class="col-md-6 {{ $errors->has('spou.*.id_card_passport') ? 'has-error' : '' }}">' +
        '<label for="id_card_passport" class="labels">IC</label>' +
        '{!! Form::text( "spou[$j][id_card_passport]", @$value, ['class' => 'form-control', 'id' => 'id_card_passport', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
        '</div>' +
      '<div class="col-md-6 {{ $errors->has('spou.*.phone') ? 'has-error' : '' }}">' +
        '<label for="phone" class="labels">PHONE NUMBER</label>' +
        '{!! Form::text( "spou[$j][phone]", @$value, ['class' => 'form-control', 'id' => 'phone', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
        '</div>' +
      '</div>' +

    '<div class="row mt-3">' +
      '<div class="col-md-6 {{ $errors->has('spou.*.dob') ? 'has-error' : '' }}">' +
        '<label for="dob" class="labels">Date Of Birth</label>' +
        '{!! Form::text( "spou[$j][dob]", @$value, ['class' => 'form-control', 'id' => 'dob', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
        '</div>' +
      '<div class="col-md-6 {{ $errors->has('spou.*.profession') ? 'has-error' : '' }}">' +
        '<label for="profession" class="labels">Profession</label>' +
        '{!! Form::text( "spou[$j][profession]", @$value, ['class' => 'form-control', 'id' => 'profession', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) !!}' +
        '</div>' +
      '</div>' +

    '<div class="mt-1 d-flex flex-row justify-content-end">' +
      '<button class="btn btn-outline-secondary btn-sm-custom bi bi-dash-lg remove_spouse"></button>' +
      '</div>' +
    '</div>'

    );

    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][id]"]'));
    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][staff_id]"]'));
    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][spouse]"]'));
    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][id_card_passport]"]'));
    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][phone]"]'));
    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][dob]"]'));
    $('#form').bootstrapValidator('addField', $('.table_spouse') .find('[name="spou['+ totalRows_spouse +'][profession]"]'));
    }
    })

    // DELETE SPOUSE
    $(".wrap_spouse_odd").on("click",".remove_spouse", function(e){
    e.preventDefault();
    var $row = $(this).parent().parent();
    var $option1 = $row.find('[name="spou['+ totalRows_spouse +'][id]"]');
    var $option2 = $row.find('[name="spou['+ totalRows_spouse +'][staff_id]"]');
    var $option3 = $row.find('[name="spou['+ totalRows_spouse +'][spouse]"]');
    var $option4 = $row.find('[name="spou['+ totalRows_spouse +'][id_card_passport]"]');
    var $option5 = $row.find('[name="spou['+ totalRows_spouse +'][phone]"]');
    var $option6 = $row.find('[name="spou['+ totalRows_spouse +'][dob]"]');
    var $option7 = $row.find('[name="spou['+ totalRows_spouse +'][profession]"]');
    $row.remove();

    $('#form').bootstrapValidator('removeField', $option1);
    $('#form').bootstrapValidator('removeField', $option2);
    $('#form').bootstrapValidator('removeField', $option3);
    $('#form').bootstrapValidator('removeField', $option4);
    $('#form').bootstrapValidator('removeField', $option5);
    $('#form').bootstrapValidator('removeField', $option6);
    $('#form').bootstrapValidator('removeField', $option7);
    console.log();
    totalRows_spouse--;
    })

    // DELETE SPOUSE
    $(".wrap_spouse_even").on("click",".remove_spouse", function(e){
    e.preventDefault();
    var $row = $(this).parent().parent();
    var $option1 = $row.find('[name="spou['+ totalRows_spouse +'][id]"]');
    var $option2 = $row.find('[name="spou['+ totalRows_spouse +'][staff_id]"]');
    var $option3 = $row.find('[name="spou['+ totalRows_spouse +'][spouse]"]');
    var $option4 = $row.find('[name="spou['+ totalRows_spouse +'][id_card_passport]"]');
    var $option5 = $row.find('[name="spou['+ totalRows_spouse +'][phone]"]');
    var $option6 = $row.find('[name="spou['+ totalRows_spouse +'][dob]"]');
    var $option7 = $row.find('[name="spou['+ totalRows_spouse +'][profession]"]');
    $row.remove();

    $('#form').bootstrapValidator('removeField', $option1);
    $('#form').bootstrapValidator('removeField', $option2);
    $('#form').bootstrapValidator('removeField', $option3);
    $('#form').bootstrapValidator('removeField', $option4);
    $('#form').bootstrapValidator('removeField', $option5);
    $('#form').bootstrapValidator('removeField', $option6);
    $('#form').bootstrapValidator('removeField', $option7);
    console.log();
    totalRows_spouse--;
    })

    @endsection