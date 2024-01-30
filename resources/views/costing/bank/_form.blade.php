<div class="card">
	<div class="card-header">Bank Clause</div>
	<div class="card-body">

		<div class="form-group row {{ $errors->has('bank')?'has-error':'' }}">
			{{ Form::label( 'it', 'Bank Clause : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::textarea('bank', @$value, ['class' => 'form-control form-control-sm', 'id' => 'it', 'placeholder' => 'Bank Clause', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Save', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
			</div>
		</div>

	</div>
</div>