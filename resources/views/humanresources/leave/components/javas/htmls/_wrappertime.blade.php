					$('#wrappertest').append(
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="1/' + obj.time_start_am + '/' + obj.time_end_am + '" id="am" checked="checked" class="form-check-input">' +
							'<label for="am" class="form-check-label m-2">' + moment(obj.time_start_am, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_am, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>' +
						'<div class="form-check form-check-inline removetest">' +
							'<input type="radio" name="half_type_id" value="2/' + obj.time_start_pm + '/' + obj.time_end_pm + '" id="pm" class="form-check-input">' +
							'<label for="pm" class="form-check-label m-2">' + moment(obj.time_start_pm, 'HH:mm:ss').format('h:mm a') + ' to ' + moment(obj.time_end_pm, 'HH:mm:ss').format('h:mm a') + '</label> ' +
						'</div>'
					);
