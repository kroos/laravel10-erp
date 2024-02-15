						$('#wrapperday').append(
							'{{ Form::label('leave_cat', 'Leave Category : ', ['class' => 'col-sm-4 col-form-label removehalfleave']) }}' +
							'<div class="col-sm-6 m-2 removehalfleave " id="halfleave">' +
								'<div class="form-check form-check-inline" id="removeleavehalf">' +
									'<input type="radio" name="leave_cat" value="1" id="radio1" class="form-check-input m-2" checked="checked">' +
									'{{ Form::label('radio1', 'Full Day Off', ['class' => 'form-check-label m-2']) }}' +
								'</div>' +
								'<div class="form-check form-check-inline" id="appendleavehalf">' +
									'<input type="radio" name="leave_cat" value="2" id="radio2" class="form-check-input m-2" >' +
									'{{ Form::label('radio2', 'Half Day Off', ['class' => 'form-check-label m-2']) }}' +
								'</div>' +
							'</div>' +
							'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
							'</div>'
						);
