					'<div class="form-group row m-2 {{ $errors->has('documentsupport') ? 'has-error' : '' }}">' +
						'<div class="offset-sm-4 col-sm-8">' +
							'<div class="form-check">' +
								'{{ Form::checkbox('documentsupport', 1, @$value, ['class' => 'form-check-input ', 'id' => 'suppdoc']) }}' +
								'<label for="suppdoc" class="form-check-label p-1 bg-warning text-danger rounded">' +
									'<p>Please ensure you will submit <strong>Supporting Documents</strong> within <strong>3 Days</strong> after date leave.</p>' +
								'</label>' +
							'</div>' +
						'</div>' +
					'</div>' +
