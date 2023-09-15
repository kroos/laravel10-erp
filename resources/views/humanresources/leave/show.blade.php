@extends('layouts.app')

@section('content')
<script>
  function printPage() {
    window.print();
  }
</script>

<style>
  @media print {
    body {
      visibility: hidden;
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
</style>

<?php
$staff = $leave->belongstostaff()->get()->first();
$login = $staff->hasmanylogin()->get()->first();
?>

<div class="table-container">
  <div class="table">
    <div class="table-row header">
      <div class="table-cell-hidden" style="width: 10%;"></div>
      <div class="table-cell" style="width: 30%;">IPMA INDUSTRY SDN.BHD.</div>
      <div class="table-cell" style="width: 50%;">LEAVE APPLICATION FORM</div>
      <div class="table-cell-hidden" style="width: 10%;"></div>
    </div>
    <div class="table-row">
      <div class="table-cell-hidden" style="width: 10%;"></div>
      <div class="table-cell" style="width: 10%;">ID : {{ @$login->username }}</div>
      <div class="table-cell" style="width: 10%;">Leave ID : HR9-{{ @str_pad($leave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ @$leave->leave_year }}</div>
      <div class="table-cell-hidden" style="width: 10%;"></div>
    </div>
  </div>

  <div class="table">
    <div class="table-row header">
      <div class="table-cell-hidden" style="width: 10%;"></div>
      <div class="table-cell" style="width: 40%;">IPMA INDUSTRY SDN.BHD.</div>
      <div class="table-cell" style="width: 40%;">LEAVE APPLICATION FORM</div>
      <div class="table-cell-hidden" style="width: 10%;"></div>
    </div>
    <div class="table-row">
      <div class="table-cell-hidden" style="width: 10%;"></div>
      <div class="table-cell" style="width: 10%;">ID : {{ @$login->username }}</div>
      <div class="table-cell" style="width: 10%;">Leave ID : HR9-{{ @str_pad($leave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ @$leave->leave_year }}</div>
      <div class="table-cell-hidden" style="width: 10%;"></div>
    </div>
  </div>

  <button onclick="printPage()">Print</button>
</div>

@endsection

@section('js')

@endsection