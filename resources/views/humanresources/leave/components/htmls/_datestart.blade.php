					'<div class="form-group row m-2 {{ $errors->has('date_time_start') ? 'has-error' : '' }}">' +
						'{{ Form::label('from', 'From : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 datetime" style="position: relative">' +
							'{{ Form::text('date_time_start', @$value, ['class' => 'form-control form-control-sm', 'id' => 'from', 'placeholder' => 'From : ', 'autocomplete' => 'off']) }}' +
						'</div>' +
					'</div>' +
