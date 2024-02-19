		$(document).on('change', '#appendleavehalf :radio', function () {
			if (this.checked) {
				var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
				var datenow =$('#from').val();

				var data1 = $.ajax({
					url: "{{ route('leavedate.timeleave') }}",
					type: "POST",
					data: {
						date: datenow,
						_token: '{!! csrf_token() !!}',
						id: {{ \Auth::user()->belongstostaff->id }}
					},
					dataType: 'json',
					global: false,
					async:false,
					success: function (response) {
						// you will get response from your php page (what you echo or print)
						return response;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				}).responseText;

				// convert data1 into json
				var obj = $.parseJSON( data1 );

				// checking so there is no double
				if( $('.removetest').length == 0 ) {
@include('humanresources.leave.components.javas.htmls._wrappertime')
					$('#form').bootstrapValidator('addField', $('.form-check').find('[name="half_type_id"]'));
				}
			}
		});

		$(document).on('change', '#removeleavehalf :radio', function () {
		//$('#removeleavehalf :radio').change(function() {
			if (this.checked) {
				$('.removetest').remove();
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_start"]'));
				$('#form').bootstrapValidator('removeField', $('.time').find('[name="time_end"]'));
			}
		});
