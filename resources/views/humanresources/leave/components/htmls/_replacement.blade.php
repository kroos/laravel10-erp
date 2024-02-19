					'<div class="form-group row m-2 {{ $errors->has('staff_id') ? 'has-error' : '' }}">' +
						'{{ Form::label('backupperson', 'Replacement : ', ['class' => 'col-sm-4 col-form-label']) }}' +
						'<div class="col-sm-8 backup">' +
							'<select name="staff_id" id="backupperson" class="form-control form-select form-select-sm " placeholder="Please choose" autocomplete="off"></select>' +
						'</div>' +
					'</div>' +
