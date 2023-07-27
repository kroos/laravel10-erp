@extends('layouts.app')

@section('content')
<style>
  .table {
    border-collapse: separate;
    border-spacing: 0 10px;
  }
</style>

<?php
$emergencies = $profile->hasmanyemergency()->get();
?>

<div class="col-auto table-responsive">

  <img src="{{ asset('storage/user_profile/' . $profile->image) }}" class="rounded mx-auto d-block" width="220" height="250">

  <table class="table">
    <tr class="my-2">
      <td class="table-primary col-md-2">
        NAME
      </td>
      <td class="col-md-5">
        {{ $profile->name }}
      </td>
      <td class="table-primary col-md-2">
        ID
      </td>
      <td>
        {{ Auth::user()->username }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        IC
      </td>
      <td class="col-md-5">
        {{ $profile->ic }}
      </td>
      <td class="table-primary col-md-2">
        PHONE NUMBER
      </td>
      <td>
        {{ $profile->mobile }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        DEPARTMENT
      </td>
      <td class="col-md-5">
        {{ $profile->belongstomanydepartment()->first()->department }}
      </td>
      <td class="table-primary col-md-2">
        SATURDAY GROUPING
      </td>
      <td>
        Group {{ $profile->restday_group_id }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        DATE OF BIRTH
      </td>
      <td class="col-md-5">
        @if ($profile->dob != NULL)
        {{ \Carbon\Carbon::parse($profile->dob)->format('d F Y') }}
        @endif
      </td>
      <td class="table-primary col-md-2">
        GENDER
      </td>
      <td>
        {{ $profile->belongstogender->gender }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        EMAIL
      </td>
      <td class="col-md-5">
        {{ $profile->email }}
      </td>
      <td class="table-primary col-md-2">
        NATIONALITY
      </td>
      <td>
        {{ $profile->belongstonationality->country }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        CATEGORY
      </td>
      <td class="col-md-5">
        {{ $profile->belongstomanydepartment->first()->belongstocategory->category }}
      </td>
      <td class="table-primary col-md-2">
        RELIGION
      </td>
      <td>
        @if ($profile->religion_id != NULL)
        {{ $profile->belongstoreligion->religion }}
        @endif
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        RACE
      </td>
      <td class="col-md-5">
        @if ($profile->race_id != NULL)
        {{ $profile->belongstorace->race }}
        @endif
      </td>
      <td class="table-primary col-md-2">
        MARITAL STATUS
      </td>
      <td>
        @if ($profile->marital_status_id != NULL)
        {{ $profile->belongstomaritalstatus->marital_status }}
        @endif
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        JOIN DATE
      </td>
      <td class="col-md-5">
        @if ($profile->join != NULL)
        {{ \Carbon\Carbon::parse($profile->join)->format('d F Y') }}
        @endif
      </td>
      <td class="table-primary col-md-2">
        CONFIRM DATE
      </td>
      <td>
        @if ($profile->confirmed != NULL)
        {{ \Carbon\Carbon::parse($profile->confirmed)->format('d F Y') }}
        @endif
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        ADDRESS
      </td>
      <td colspan="3" class="col-md-5">
        {{ $profile->address }}
      </td>
    </tr>
  </table>

  @if ($emergencies->isNotEmpty())
  <table class="table">
    <tr>
      <td class="table-success">
        EMERGENCY CONTACT
      </td>
    </tr>
    @foreach ($emergencies as $emergency)
    <tr class="my-2">
      <td class="table-primary col-md-2">
        NAME
      </td>
      <td class="col-md-5">
        {{ $emergency->contact_person }}
      </td>
      <td class="table-primary col-md-2">
        RELATIONSHIP
      </td>
      <td>
        {{ $emergency->belongstorelationship->relationship}}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        ADDRESS
      </td>
      <td class="col-md-5">
        {{ $emergency->address }}
      </td>
      <td class="table-primary col-md-2">
        PHONE
      </td>
      <td>
        {{ $emergency->phone }}
      </td>
    </tr>
    @endforeach
  </table>
  @endif

</div>
@endsection

@section('js')

@endsection