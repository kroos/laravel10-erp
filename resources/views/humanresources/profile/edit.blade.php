@extends ('layouts.app')

@section('content')
<!-- <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://unpkg.com/bootstrap-show-password@1.2.1/dist/bootstrap-show-password.min.js"></script> -->





<style>
  /* .form-control, #ic {
border: 1px solid black;
} */

  /* div {
border: 1px solid black;
} */
</style>

<div class="container rounded bg-white mt-2 mb-2">

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

        {!! Form::model($profile, ['route' => ['profile.update', $profile->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}
        <input type="hidden" name="login_id" id="login_id" value="{{ $profile->hasmanylogin()->where('active', 1)->first()->id }}">

        <div class="row">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="text-right">Edit Staff Password</h4>
          </div>
        </div>
        <div class="row mb-5">
          <div class="col-md-12">
            <div class="row mt-3">
              <div class="col-md-2">
                <label for="ic" class="labels">ID</label>
              </div>
              <div class="col-md-10">
                <input type="text" class="form-control" value="{{ $profile->hasmanylogin()->where('active', 1)->first()->username }}" readonly>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-2">
                <label for="ic" class="labels">Name</label>
              </div>
              <div class="col-md-10">
                <input type="text" class="form-control" value="{{ $profile->name }}" readonly>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-2">
                <label for="ic" class="labels">New Password</label>
              </div>
              <div class="col-md-10 {{ $errors->has('password') ? 'has-error' : '' }}">
              {!! Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder' => 'Password', 'data-toggle' => 'password']) !!}
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-2">
                <label for="ic" class="labels">Confirm Password</label>
              </div>
              <div class="col-md-10 {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
              {!! Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'password_confirmation', 'placeholder' => 'Confirm Password']) !!}
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="text-center">
              {!! Form::button('Save', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
            </div>
          </div>

        {!! Form::close() !!}

          <div class="row mt-4">
            <div class="text-center">
              <a href="{{ url()->previous() }}">
                <button class="btn btn-sm btn-outline-secondary">Back</button>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection

  @section('js')

  @endsection