					'<div class="form-group row m-2 {{ $errors->has('date_time_end') ? 'has-error' : '' }}">' +
						'{{ Form::label('to', 'To : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 datetime" style="position: relative">' +
							'{{ Form::text('date_time_end', @$value, ['class' => 'form-control form-control-sm', 'id' => 'to', 'placeholder' => 'To : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +
