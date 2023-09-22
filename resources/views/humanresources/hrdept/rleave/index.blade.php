@extends('layouts.app')

@section('content')

<style>
  /* div {
  border: 1px solid black;
} */
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')
  <h4>Replacement Leave</h4>
  <div>
    <table id="replacement" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Date Start</th>
          <th>Date End</th>
          <th>Customer</th>
          <th>Reason</th>
          <th>Total</th>
          <th>Utilize</th>
          <th>Balance</th>
          <th>Remarks</th>
          <th>Edit</th>
          <th>Cancel</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($replacements as $replacement)
        <tr>
          <th>ID</th>
          <th>{{ $replacement->belongstostaff->name }}</th>
          <th>{{ $replacement->date_start }}</th>
          <th>{{ $replacement->date_end }}</th>
          <th>
            @if ($replacement->belongstocustomer)
              {{ $replacement->belongstocustomer->customer }}
            @endif
          </th>
          <th>{{ $replacement->reason }}</th>
          <th>Total</th>
          <th>Utilize</th>
          <th>Balance</th>
          <th>Remarks</th>
          <th>Edit</th>
          <th>Cancel</th>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="d-flex justify-content-center">
      {{ $replacements->links() }}
    </div>
  </div>
</div>
@endsection




















@section('js')

@endsection

@section('nonjquery')

@endsection