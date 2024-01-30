<div class="card">
	<div class="card-header">Product / Item</div>
	<div class="card-body">

		<div class="form-group row {{ $errors->has('item')?'has-error':'' }}">
			{{ Form::label( 'it', 'Product / Item : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('item', @$value, ['class' => 'form-control', 'id' => 'it', 'placeholder' => 'Product / Item', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('info')?'has-error':'' }}">
			{{ Form::label( 'inf', 'Info : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('info', @$value, ['class' => 'form-control', 'id' => 'inf', 'placeholder' => 'Info', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('price')?'has-error':'' }}">
			{{ Form::label( 'pri', 'Price : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('price', @$value, ['class' => 'form-control', 'id' => 'pri', 'placeholder' => 'Price', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('remarks')?'has-error':'' }}">
			{{ Form::label( 'rem', 'Remarks : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('remarks', @$value, ['class' => 'form-control', 'id' => 'rem', 'placeholder' => 'Remarks', 'aria-describedby' => 'emailHelp']) !!}
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Save', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
			</div>
		</div>

	</div>
</div>