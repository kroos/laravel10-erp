@extends('layouts.app')

@section('content')
<style>
  .table {
    border-collapse: separate;
    border-spacing: 0 10px;
  }
</style>

<?php
$us = \Auth::user()->belongstostaff;
$login = \Auth::user();
?>

<div class="col-auto table-responsive">

  <img src="{{ asset('storage/user_profile/' . $us->image) }}" class="rounded mx-auto d-block" width="220" height="250">

  <table class="table">
    <tr class="my-2">
      <td class="table-primary col-md-2">
        NAME
      </td>
      <td class="col-md-5">
        {{ $us->name }}
      </td>
      <td class="table-primary col-md-2">
        ID
      </td>
      <td>
        {{ $login->username }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        IC
      </td>
      <td class="col-md-5">
        {{ $us->ic }}
      </td>
      <td class="table-primary col-md-2">
        PHONE NUMBER
      </td>
      <td>
        {{ $us->mobile }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        DEPARTMENT
      </td>
      <td class="col-md-5">
        {{ $us->belongstomanydepartment->first()->department }}
      </td>
      <td class="table-primary col-md-2">
        SATURDAY GROUPING
      </td>
      <td>
        Group {{ $us->restday_group_id }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        DATE OF BIRTH
      </td>
      <td class="col-md-5">
        @if ($us->dob != NULL)
        {{ \Carbon\Carbon::parse($us->dob)->format('d F Y') }}
        @endif
      </td>
      <td class="table-primary col-md-2">
        GENDER
      </td>
      <td>
        {{ $us->belongstogender->gender }}
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        EMAIL
      </td>
      <td class="col-md-5">
        {{ $us->email }}
      </td>
      <td class="table-primary col-md-2">
        NATIONALITY
      </td>
      <td>
        {{ $us->belongstonationality->country }}
      </td>
    </tr>

    <tr class="my-2">
      <td class="table-primary col-md-2">
        CATEGORY
      </td>
      <td class="col-md-5">
        {{ $us->email }}
      </td>
      <td class="table-primary col-md-2">
        RELIGION
      </td>
      <td>
        {{ $us->belongstonationality->country }}
      </td>
    </tr>



    <tr class="my-2">
      <td class="table-primary col-md-2">
        JOIN DATE
      </td>
      <td class="col-md-5">
        @if ($us->join != NULL)
        {{ \Carbon\Carbon::parse($us->join)->format('d F Y') }}
        @endif
      </td>
      <td class="table-primary col-md-2">
        CONFIRM DATE
      </td>
      <td>
        @if ($us->confirmed != NULL)
        {{ \Carbon\Carbon::parse($us->confirmed)->format('d F Y') }}
        @endif
      </td>
    </tr>
    <tr class="my-2">
      <td class="table-primary col-md-2">
        ADDRESS
      </td>
      <td colspan="3" class="col-md-5">
        {{ $us->address }}
      </td>
    </tr>
  </table>
</div>



@endsection

@section('js')

@endsection