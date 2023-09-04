@extends('layouts.app')

@section('content')
<style>
    .table {
        display: table;
        width: 100%;
        border-collapse: collapse;
    }

    .table-row {
        display: table-row;
    }

    .table-cell {
        display: table-cell;
        border: 1px solid #b3b3b3;
        padding: 4px;
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

<div class="table">
    <div class="table-row header">
        <div class="table-cell col-md-3">
            IPMA INDUSTRY SDN.BHD.
        </div>
        <div class="table-cell theme col-md-9">
            LEAVE APPLICATION FORM
        </div>
    </div>

    <div class="table-row">
        <div class="table-cell col-md-3">
            ID : {{ @$login->username }}
        </div>
        <div class="table-cell theme col-md-9">
            Leave ID : HR9-{{ @str_pad($leave->leave_no,5,'0',STR_PAD_LEFT) }}/{{ @$leave->leave_year }}
        </div>
    </div>
</div>
@endsection

@section('js')

@endsection