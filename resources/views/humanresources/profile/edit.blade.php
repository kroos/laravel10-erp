@extends ('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	<h4>Edit Staff Password</h4>
	<div class="col-sm-2 text-center">
		<img class="rounded mb-3" width="180px" src="{{ asset('storage/user_profile/' . $profile->image) }}">
		<span class="font-weight-bold">ID: {{ $profile->hasmanylogin()->where('active', 1)->first()->username }}</span>
	</div>

	<div class="col-md-10 row">
		<div class="col-md-12">
			{!! Form::model($profile, ['route' => ['profile.update', $profile->id], 'method' => 'PATCH', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) !!}
			<input type="hidden" name="login_id" id="login_id" value="{{ $profile->hasmanylogin()->where('active', 1)->first()->id }}">

			<div class="row m-2">
				<label for="ic" class="form-label col-sm-4">ID</label>
				<div class="col-sm-8">
					<input type="text" class="form-control form-control-sm" value="{{ $profile->hasmanylogin()->where('active', 1)->first()->username }}" readonly>
				</div>
			</div>

			<div class="row m-2">
				<label for="ic" class="form-label col-sm-4">Name</label>
				<div class="col-sm-8">
					<input type="text" class="form-control form-control-sm" value="{{ $profile->name }}" readonly>
				</div>
			</div>

			<div class="row m-2">
				<label for="ic" class="form-label col-sm-4">New Password</label>
				<div class="col-sm-8 {{ $errors->has('password') ? 'has-error' : '' }}">
					{!! Form::password('password', ['class' => 'form-control form-control-sm', 'id' => 'password', 'placeholder' => 'Password', 'data-toggle' => 'password']) !!}
				</div>
			</div>

			<div class="row m-2">
				<label for="ic" class="form-label col-sm-4">Confirm Password</label>
				<div class="col-sm-8 {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
					{!! Form::password('password_confirmation', ['class' => 'form-control form-control-sm', 'id' => 'password_confirmation', 'placeholder' => 'Confirm Password']) !!}
				</div>
			</div>

			<div class="row m-2">
				<div class="col-sm-8 offset-sm-4">
					{!! Form::button('Save', ['class' => 'btn btn-sm btn-outline-secondary', 'type' => 'submit']) !!}
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection

@section('js')

@endsection
