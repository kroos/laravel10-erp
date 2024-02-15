					if(d === true) {
@include('humanresources.leave.components.javas.htmls._wrapperdaya')
						$('#form').bootstrapValidator('addField', $('.form-check').find('[name="leave_cat"]'));
						$('#form').bootstrapValidator('addField', $('.form-check').find('[name="half_type_id"]'));

						var daynow = moment($('#from').val(), 'YYYY-MM-DD').format('dddd');
						var datenow =$('#from').val();

						var data1 = $.ajax({
							url: "{{ route('leavedate.timeleave') }}",
							type: "POST",
							data: {date: datenow, _token: '{!! csrf_token() !!}', id: {{ \Auth::user()->belongstostaff->id }} },
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

						var checkedam = 'checked';
						var checkedpm = 'checked';
						if(obj.time_start_am == itime_start) {
							var toggle_time_start_am = 'disabled';
							var checkedam = '';
							var checkedpm = 'checked';
						}

						if(obj.time_start_pm == itime_start) {
							var toggle_time_start_pm = 'disabled';
							var checkedam = 'checked';
							var checkedpm = '';
						}
@include('humanresources.leave.components.javas.htmls._wrappertest')
						$('#form').bootstrapValidator('addField', $('.form-check').find('[name="leave_cat"]'));
						$('#form').bootstrapValidator('addField', $('.form-check').find('[name="half_type_id"]'));

					} else {
@include('humanresources.leave.components.javas.htmls._wrapperdayb')
						$('#form').bootstrapValidator('addField', $('.form-check').find('[name="leave_cat"]'));
					}
