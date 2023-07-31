@extends('layouts.app')

@section('content')

{!! Form::model($profile, ['route' => ['profile.update', $profile->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}

<div class="container rounded bg-white mt-5 mb-5">
  <div class="row">
    <div class="col-md-3 border-right">
      <div class="d-flex flex-column align-items-center text-center p-3 py-5">
        <img class="rounded-5 mt-3" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
        <span class="font-weight-bold">{{ $profile->name }}</span>
        <span class="font-weight-bold">{{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</span>
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
            <label class="labels">Name</label>
            <input type="text" class="form-control" placeholder="enter name" value="{{ $profile->name }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">IC</label>
            {{ Form::text( 'ic', @$value, ['class' => 'form-control', 'id' => 'ic', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
          <div class="col-md-6">
            <label class="labels">PHONE NUMBER</label>
            {{ Form::text( 'mobile', @$value, ['class' => 'form-control', 'id' => 'mobile', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">EMAIL</label>
            {{ Form::text( 'email', @$value, ['class' => 'form-control', 'id' => 'email', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">ADDRESS</label>
            {{ Form::text( 'address', @$value, ['class' => 'form-control', 'id' => 'address', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">DEPARTMENT</label>
            <input type="text" class="form-control" placeholder="enter name" value="{{ $profile->belongstomanydepartment()->first()->department }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">CATEGORY</label>
            <input type="text" class="form-control" placeholder="enter name" value="{{ $profile->belongstomanydepartment->first()->belongstocategory->category }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">SATURDAY GROUPING</label>
            <input type="text" class="form-control" placeholder="enter name" value="Group {{ $profile->restday_group_id }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">DATE OF BIRTH</label>
            {!! Form::date( 'dob', @$value, ['class' => 'form-control', 'id' => 'dob', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6">
            <label class="labels">GENDER</label>
            {!! Form::select( 'gender_id', $gender, @$value, ['class' => 'form-control', 'id' => 'gender_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">NATIONALITY</label>
            {!! Form::select( 'nationality_id', $nationality, @$value, ['class' => 'form-control', 'id' => 'nationality_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6">
            <label class="labels">RACE</label>
            {!! Form::select( 'race_id', $race, @$value, ['class' => 'form-control', 'id' => 'race_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">RELIGION</label>
            {!! Form::select( 'religion_id', $religion, @$value, ['class' => 'form-control', 'id' => 'religion_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6">
            <label class="labels">MARITAL STATUS</label>
            {!! Form::select( 'marital_status_id', $marital_status, @$value, ['class' => 'form-control', 'id' => 'marital_status_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">JOIN DATE</label>
            <input type="text" class="form-control" placeholder="enter name" value="{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">CONFIRM DATE</label>
            <input type="text" class="form-control" placeholder="enter name" value="{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}" readonly>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 border-right">
      <div class="p-3 py-5">

      <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="text-right">Emergency Contact</h4>
          <span class="border px-3 p-1 add-experience btn btn-sm btn-outline-secondary">
            <i class="fa fa-plus"></i>&nbsp;Contact
          </span>
        </div>

        @if ($emergencies->isNotEmpty())
        @foreach ($emergencies as $emergency)
        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">NAME</label>
            {{ Form::text( 'contact_person', @$emergency->contact_person, ['class' => 'form-control', 'id' => 'contact_person', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">RELATIONSHIP</label>
            {!! Form::select( 'relationship_id', $relationship, @$emergency->relationship_id, ['class' => 'form-control', 'id' => 'relationship_id', 'placeholder' => 'Please Select', 'autocomplete' => 'off'] ) !!}
          </div>
          <div class="col-md-6">
            <label class="labels">PHONE NUMBER</label>
            {{ Form::text( 'phone', @$emergency->phone, ['class' => 'form-control', 'id' => 'phone', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">ADDRESS</label>
            {{ Form::text( 'emergency_address', @$emergency->address, ['class' => 'form-control', 'id' => 'address', 'placeholder' => 'Please Insert', 'autocomplete' => 'off'] ) }}
          </div>
        </div>

        <div class="row mt-4"></div>
        @endforeach
        @endif
        
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-9">
      <div class="text-center">
        {!! Form::button('Save', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
      </div>
    </div>
  </div>
</div>
{{ Form::close() }}

@endsection

@section('js')

@endsection