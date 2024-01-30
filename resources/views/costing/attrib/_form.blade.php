<div class="card">
	<div class="card-header">Product Attribute</div>
	<div class="card-body">

		<div class="form-group row {{ $errors->has('attribute')?'has-error':'' }}">
			{{ Form::label( 'it', 'Product Attribute : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('attribute', @$value, ['class' => 'form-control form-control-sm', 'id' => 'it', 'placeholder' => 'Product Attibute', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Save', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
			</div>
		</div>

	</div>
</div>