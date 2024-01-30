<div class="card">
	<div class="card-header">UOM Delivery Date Period</div>
	<div class="card-body">

		<div class="form-group row {{ $errors->has('delivery_date_period')?'has-error':'' }}">
			{{ Form::label( 'delivery_date_period', 'UOM Delivery Date Period : ', ['class' => 'col-sm-2 col-form-label'] ) }}
			<div class="col-sm-10">
				{!! Form::text('delivery_date_period', @$value, ['class' => 'form-control', 'id' => 'delivery_date_period', 'placeholder' => 'UOM Delivery Date Period', 'aria-describedby' => 'emailHelp']) !!}
				<small id="emailHelp" class="form-text text-muted">Please make sure the period is plural.</small>
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 offset-sm-2">
				{!! Form::button('Save', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
			</div>
		</div>

	</div>
</div>