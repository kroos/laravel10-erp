@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<div class="col-sm-12">
			<canvas id="myChart" width="200" height="75"></canvas>
		</div>
	</div>
</div>
@endsection

@section('js')
/////////////////////////////////////////////////////////////////////////////////////////
@endsection

@section('nonjquery')
/////////////////////////////////////////////////////////////////////////////////////////
// chartjs also dont use jquery

var xmlhttp = new XMLHttpRequest();
// xmlhttp.open(method, URL, [async, user, password])
xmlhttp.open("POST", '{!! route('staffdaily', ['_token' => csrf_token()]) !!}', true);
// xmlhttp.responseType = 'json';
// xmlhttp.onreadystatechange = myfunction;
xmlhttp.send();
xmlhttp.onload = function() {
// alert(`Loaded: ${data.status} ${data.response}`);
// return data.status;
	const data = JSON.parse(xmlhttp.responseText);
//	console.log(data);

	new Chart(document.getElementById('myChart'), {
		// type: 'bar',
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
//						{
//							type: 'bar',
//							label: 'On Leave Staff From ' + data.map(obj => {
//																		// let d = Object.entries(obj.locationleave);
//																		const d = obj.locationleave;
//																		return Object.keys(d);
//																	}),
//							data: data.map(obj => {
//								// let d = Object.entries(obj.locationleave);
//								const d = obj.locationleave;
//								return Object.values(d);
//							}),
//						},
//						{
//							type: 'bar',
//							label: 'Absent Staff From ' + data.map(obj => {
//																		// let d = Object.entries(obj.locationabsent);
//																		const d = obj.locationabsent;
//																		return Object.keys(d);
//																	}),
//							data: data.map(obj => {
//								// let d = Object.entries(obj.locationabsent);
//								const d = obj.locationabsent;
//								return Object.values(d);
//							}),
//						},
//						{
//							type: 'bar',
//							label: 'Half Day Absent Staff From ' + data.map(obj => {
//																		// let d = Object.entries(obj.locationhalfabsent);
//																		const d = obj.locationhalfabsent;
//																		return Object.keys(d);
//																	}),
//							data: data.map(obj => {
//								// let d = Object.entries(obj.locationhalfabsent);
//								const d = obj.locationhalfabsent;
//								return Object.values(d);
//							}),
//						},
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
};

/////////////////////////////////////////////////////////////////////////////////////////
@endsection


