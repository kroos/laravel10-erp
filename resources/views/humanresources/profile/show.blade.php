@extends('layouts.app')

@section('content')

<?php
$emergencies = $profile->hasmanyemergency()->get();
?>

<div class="container rounded bg-white mt-2 mb-2">
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
            <input type="text" class="form-control" value="{{ $profile->name }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">IC</label>
            <input type="text" class="form-control" value="{{ $profile->ic }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">PHONE NUMBER</label>
            <input type="text" class="form-control" value="{{ $profile->mobile }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">EMAIL</label>
            <input type="text" class="form-control" value="{{ $profile->email }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">ADDRESS</label>
            <input type="text" class="form-control" value="{{ $profile->address }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <label class="labels">DEPARTMENT</label>
            <input type="text" class="form-control" value="{{ $profile->belongstomanydepartment()->first()->department }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">CATEGORY</label>
            <input type="text" class="form-control" value="{{ $profile->belongstomanydepartment->first()->belongstocategory->category }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">SATURDAY GROUPING</label>
            <input type="text" class="form-control" value="Group {{ $profile->restday_group_id }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">DATE OF BIRTH</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}" readonly>

          </div>
          <div class="col-md-6">
            <label class="labels">GENDER</label>
            <input type="text" class="form-control" value="{{ $profile->belongstogender->gender }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">NATIONALITY</label>
            <input type="text" class="form-control" value="{{ $profile->belongstonationality->country }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">RACE</label>
            <input type="text" class="form-control" value="{{ $profile->belongstorace->race }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">RELIGION</label>
            <input type="text" class="form-control" value="{{ $profile->belongstoreligion->religion }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">MARITAL STATUS</label>
            <input type="text" class="form-control" value="{{ $profile->belongstomaritalstatus->marital_status }}" readonly>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label class="labels">JOIN DATE</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}" readonly>
          </div>
          <div class="col-md-6">
            <label class="labels">CONFIRM DATE</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}" readonly>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 border-right">
      <div class="p-3 py-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="text-right">Emergency Contact</h4>
        </div>

        @if ($emergencies->isNotEmpty())
        @foreach ($emergencies as $emergency)

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
        <a href="{{ route('profile.edit', $profile->id) }}">
          <button type="button" class="btn btn-sm btn-outline-secondary">EDIT</button>
        </a>
      </div>
    </div>
  </div>

</div>
@endsection

@section('js')

@endsection