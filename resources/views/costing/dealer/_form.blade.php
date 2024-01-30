<div class="card">
	<div class="card-header">Dealer Clause</div>
	<div class="card-body">

		<div class="form-group row {{ $errors->has('dealer')?'has-error':'' }}">
			{{ Form::label( 'it', 'Dealer Clause : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('dealer', @$value, ['class' => 'form-control form-control-sm', 'id' => 'it', 'placeholder' => 'Dealer Clause', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Save', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
			</div>
		</div>

	</div>
</div>