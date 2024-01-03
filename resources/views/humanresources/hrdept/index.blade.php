@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<div class="col-sm-12 m-3">
			<h4>Overall Summary</h4>
			<div class="table-responsive">
				<table class="table table-hover table-sm align-middle table-border" style="font-size:12px">
					<thead>
						<tr>
							<th class="text-center">Date</th>
							<th class="text-center">Day Status</th>
							<th class="text-center">Percentage</th>
							<th class="text-center">Available Staff</th>
							<th class="text-center" colspan="2">Outstation</th>
							<th class="text-center" colspan="2">On Leave</th>
							<th class="text-center" colspan="2">Absents</th>
							<th class="text-center" colspan="2">Half Absents</th>
							<th class="text-center">Total Staff</th>
						</tr>
					</thead>
					<tbody id="summary">
					</tbody>
				</table>
			</div>
		</div>
		<div class="col-sm-12 m-3">
			<canvas id="myChart" width="200" height="75"></canvas>
		</div>
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
var data1 = $.ajax({
	url: "{{ route('staffdaily', ['_token' => csrf_token()]) }}",
	type: "POST",
	dataType: 'json',
	global: false,
	async: false,		// this must be false for var data is available
	always: function(data, textStatus, jqXHR){

	},
	success: function (response, status, xhr) {
		// console.log([response, status, xhr]);
		return response;
	},
	error: function(jqXHR, textStatus, errorThrown) {
		// console.log(textStatus, errorThrown);
	}
}).responseText;
var data = $.parseJSON( data1 );

var i = 0;
$.each(data, function( index, value ) {
	$('#summary').append(
		'<tr>' +
			'<td class="text-center">' + value.date + '</td>' +
			'<td class="text-center">' + value.working + '</td>' +
			'<td class="text-center">' + value.overallpercentage + '%</td>' +
			'<td class="text-center">' + value.workingpeople + '</td>' +
			'<td class="text-center" colspan="2">' + value.outstation + '</td>' +
			'<td class="text-center" colspan="2">' + value.leave + '</td>' +
			'<td class="text-center" colspan="2">' + value.absent + '</td>' +
			'<td class="text-center" colspan="2">' + value.halfabsent + '</td>' +
			'<td class="text-center">' + value.workday + '</td>' +
		'</tr>' +
		'<tr>' +
			'<td class="text-center" colspan="4"></td>' +
			'<td class="text-center" colspan="2" id="infoa' + i + '"></td>' +
			'<td class="text-center" colspan="2" id="infob' + i + '"></td>' +
			'<td class="text-center" colspan="2" id="infoc' + i + '"></td>' +
			'<td class="text-center" colspan="2" id="infod' + i + '"></td>' +
			'<td class="text-center"></td>' +
		'</tr>'
	);

	// console.log(value);
	// console.log(value.locoutstation);
	// console.log(value.locationleave);
	// console.log(value.locationabsent);
	// console.log(value.locationhalfabsent);
	// console.log($.isEmptyObject(value.locoutstation));
	// console.log($.isEmptyObject(value.locationleave));
	// console.log($.isEmptyObject(value.locationabsent));
	// console.log($.isEmptyObject(value.locationhalfabsent));

	$.each(value.locoutstation, function( ind, val ) {
		if ($.isEmptyObject(value.locoutstation)) {
			// $('#infoa' + i).append();
		} else {
			$('#infoa' + i).append(
				ind + ' : ' + val + '<br/>'
			);
		}
	});

	$.each(value.locationleave, function( ind, val ) {
		if ($.isEmptyObject(value.locationleave)) {
			// $('#infob' + i).append();
		} else {
			$('#infob' + i).append(
				ind + ' : ' + val + '<br/>'
			);
		}
	});

	$.each(value.locationabsent, function( ind, val ) {
		if ($.isEmptyObject(value.locationabsent)) {
			// $('#infoc' + i).append();
		} else {
			$('#infoc' + i).append(
				ind + ' : ' + val + '<br/>'
			);
		}
	});

	$.each(value.locationhalfabsent, function( ind, val ) {
		if ($.isEmptyObject(value.locationhalfabsent)) {
			// $('#infod' + i).append();
		} else {
			$('#infod' + i).append(
				ind + ' : ' + val + '<br/>'
			);
		}
	});

	i++;
});



new Chart(document.getElementById('myChart'), {
	data: {
		labels: data.map(row => [row.date, row.working]),
		datasets: [
					{
						type: 'line',
						label: 'Total Attendance Percentage By Day(%)',
						data: data.map(row => row.overallpercentage),
						tension: 0.3,
					},
					{
						type: 'bar',
						label: 'Available Staff',
						data: data.map(row => row.workingpeople)
					},
					{
						type: 'bar',
						label: 'Outstation',
						data: data.map(row => row.outstation)
					},
					{
						type: 'bar',
						label: 'On Leave',
						data: data.map(row => row.leave)
					},
					{
						type: 'bar',
						label: 'Absents',
						data: data.map(row => row.absent)
					},
					{
						type: 'bar',
						label: 'Half Absents',
						data: data.map(row => row.halfabsent)
					},
					{
						type: 'bar',
						label: 'Total Staff',
						data: data.map(row => row.workday)
					},
					// {
					// 	type: 'bar',
					// 	label:
					// 														// data.forEach((s, index, array) => {
					// 														// 	const d = s.locoutstation;
					// 														// 	// console.log(d);
					// 														// 	// console.log(Object.keys(d));
					// 														// 	// console.log(Object.keys(array[index].locoutstation));
					// 														// 	return Object.keys(array[index].locoutstation);
					// 														// })
					// 											data.map((obj, index) => {
					// 												const d = obj.locoutstation;
					// 												console.log(Object.keys(d))
					// 											})
					// 											,
					// 	data: data.map(obj => {
					// 							// let d = Object.entries(obj.locoutstation);
					// 							const d = obj.locoutstation;
					// 							return Object.values(d);
					// 						})
					// },
					// {
					// 	type: 'bar',
					// 	label: 'On Leave Staff From ' + data.map(obj => {
					// 												// let d = Object.entries(obj.locationleave);
					// 												const d = obj.locationleave;
					// 												return Object.keys(d);
					// 											}),
					// 	data: data.map(obj => {
					// 		// let d = Object.entries(obj.locationleave);
					// 		const d = obj.locationleave;
					// 		return Object.values(d);
					// 	}),
					// },
					// {
					// 	type: 'bar',
					// 	label: 'Absent Staff From ' + data.map(obj => {
					// 												// let d = Object.entries(obj.locationabsent);
					// 												const d = obj.locationabsent;
					// 												return Object.keys(d);
					// 											}),
					// 	data: data.map(obj => {
					// 		// let d = Object.entries(obj.locationabsent);
					// 		const d = obj.locationabsent;
					// 		return Object.values(d);
					// 	}),
					// },
					// {
					// 	type: 'bar',
					// 	label: 'Half Day Absent Staff From ' + data.map(obj => {
					// 												// let d = Object.entries(obj.locationhalfabsent);
					// 												const d = obj.locationhalfabsent;
					// 												return Object.keys(d);
					// 											}),
					// 	data: data.map(obj => {
					// 		// let d = Object.entries(obj.locationhalfabsent);
					// 		const d = obj.locationhalfabsent;
					// 		return Object.values(d);
					// 	}),
					// },
		],
	},
	options: {
		responsive: true,
		scales: {
			y: {
				beginAtZero: true
			}
		},
		interaction: {
			intersect: false,
			mode: 'index',
		},
	},
	plugins: {
		legend: {
			position: 'top',
		},
		title: {
			display: true,
			text: 'Attendance Statistic Daily'
		},
	},
});

@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
@endsection


