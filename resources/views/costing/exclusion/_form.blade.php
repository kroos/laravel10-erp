<div class="card">
	<div class="card-header">Exclusion</div>
	<div class="card-body">

		<div class="form-group row {{ $errors->has('quot_remarks')?'has-error':'' }}">
			{{ Form::label( 'it', 'Exclusion : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('exclusion', @$value, ['class' => 'form-control form-control-sm', 'id' => 'it', 'placeholder' => 'Exclusion', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Save', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
			</div>
		</div>

	</div>
</div>