		$('#remove').remove();
		if($selection.val() == '3') {
			$('#wrapper').append(
				'<div id="remove">' +
					<!-- UNPAID LEAVE | UPL -->
@include('humanresources.leave.components.htmls._datestart')
@include('humanresources.leave.components.htmls._dateend')

					'<div class="form-group row m-2 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
						'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>' +
					'</div>' +

					@if( $userneedbackup == 1 )
@include('humanresources.leave.components.htmls._replacement')
					@endif

					'<div class="form-group row m-2 {{ $errors->has('document') ? 'has-error' : '' }}">' +
						'{{ Form::label( 'doc', 'Upload Supporting Document : ', ['class' => 'col-sm-4 col-form-label'] ) }}' +
						'<div class="col-sm-8 supportdoc">' +
							'{{ Form::file( 'document', ['class' => 'form-control form-control-sm form-control-file', 'id' => 'doc', 'placeholder' => 'Supporting Document']) }}' +
						'</div>' +
					'</div>' +

@include('humanresources.leave.components.htmls._documentsupport')

				'</div>'
			);
		} else {
			$('#wrapper').append(
				'<div id="remove">' +
					<!-- ANNUAL LEAVE | AL -->
@include('humanresources.leave.components.htmls._datestart')
@include('humanresources.leave.components.htmls._dateend')

					'<div class="form-group row m-2 {{ $errors->has('leave_cat') ? 'has-error' : '' }}" id="wrapperday">' +
						'<div class="form-group col-sm-8 offset-sm-4 {{ $errors->has('half_type_id') ? 'has-error' : '' }} removehalfleave"  id="wrappertest">' +
						'</div>' +
					'</div>' +

					@if( $userneedbackup == 1 )
@include('humanresources.leave.components.htmls._replacement')
					@endif
				'</div>'
			);
		}

		@if( $userneedbackup == 1 )
		$('#form').bootstrapValidator('addField', $('.backup').find('[name="staff_id"]'));
		@endif
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_start"]'));
		$('#form').bootstrapValidator('addField', $('.datetime').find('[name="date_time_end"]'));
		if($selection.val() == '3') {
			$('#form').bootstrapValidator('addField', $('.supportdoc').find('[name="document"]'));
			$('#form').bootstrapValidator('addField', $('.suppdoc').find('[name="documentsupport"]'));
		}

		/////////////////////////////////////////////////////////////////////////////////////////
		//enable select 2 for backup
		$('#backupperson').select2({
			placeholder: 'Please Choose',
			width: '100%',
			ajax: {
				url: '{{ route('backupperson') }}',
				// data: { '_token': '{!! csrf_token() !!}' },
				type: 'POST',
				dataType: 'json',
				data: function (params) {
					var query = {
						id: {{ \Auth::user()->belongstostaff->id }},
						_token: '{!! csrf_token() !!}',
						date_from: $('#from').val(),
						date_to: $('#to').val(),
					}
					return query;
				}
			},
			allowClear: true,
			closeOnSelect: true,
		});

		/////////////////////////////////////////////////////////////////////////////////////////
		// start date
		$('#from').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fa-regular fa-circle-up fa-beat",
				down: "fa-regular fa-circle-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			minDate: moment().format('YYYY-MM-DD'),
			disabledDates: data,
			// daysOfWeekDisabled: [0],
			// minDate: data[1],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_start');
			var minDaten = $('#from').val();
			// console.log(minDaten);
			$('#to').datetimepicker('minDate', minDaten);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
						// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#from').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
@include('humanresources.leave.components.javas._halfday')
				}
			}
			if($('#from').val() !== $('#to').val()) {
				// $('.form-check').find('[name="leave_cat"]').css('border', '3px solid black');
				$('#form').bootstrapValidator('removeField', $('.form-check').find('[name="leave_cat"]'));
				$('#form').bootstrapValidator('removeField', $('.form-check').find('[name="half_type_id"]'));
				$('.removehalfleave').remove();
			}
		});
		// end date from

		$('#to').datetimepicker({
			icons: {
				time: "fas fas-regular fa-clock fa-beat",
				date: "fas fas-regular fa-calendar fa-beat",
				up: "fas fas-regular fa-arrow-up fa-beat",
				down: "fas fas-regular fa-arrow-down fa-beat",
				previous: 'fas fas-regular fa-arrow-left fa-beat',
				next: 'fas fas-regular fa-arrow-right fa-beat',
				today: 'fas fas-regular fa-calenday-day fa-beat',
				clear: 'fas fas-regular fa-broom-wide fa-beat',
				close: 'fas fas-regular fa-rectangle-xmark fa-beat'
			},
			format:'YYYY-MM-DD',
			useCurrent: false,
			minDate: moment().format('YYYY-MM-DD'),
			disabledDates:data,
			//daysOfWeekDisabled: [0],
		})
		.on('dp.change dp.update', function(e) {
			$('#form').bootstrapValidator('revalidateField', 'date_time_end');
			var maxDate = $('#to').val();
			$('#from').datetimepicker('maxDate', maxDate);
			if($('#from').val() === $('#to').val()) {
				if( $('.removehalfleave').length === 0) {

					////////////////////////////////////////////////////////////////////////////////////////
					// checking half day leave
					var d = false;
					var itime_start = 0;
					var itime_end = 0;
					$.each(objtime, function() {
					// console.log(this.date_half_leave);
						if(this.date_half_leave == $('#to').val()) {
							return [d = true, itime_start = this.time_start, itime_end = this.time_end];
						}
					});
@include('humanresources.leave.components.javas._halfday')
				}
			}
			if($('#from').val() !== $('#to').val()) {
				$('#form').bootstrapValidator('removeField', $('.form-check').find('[name="leave_cat"]'));
				$('#form').bootstrapValidator('removeField', $('.form-check').find('[name="half_type_id"]'));
				$('.removehalfleave').remove();
			}
		});
		// end date to

		/////////////////////////////////////////////////////////////////////////////////////////
		// enable radio
@include('humanresources.leave.components.javas._appendleavehalf')
