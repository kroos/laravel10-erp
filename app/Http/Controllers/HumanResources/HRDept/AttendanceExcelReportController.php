<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load laravel-Excel
// use App\Exports\PayslipExport;
// use Maatwebsite\Excel\Facades\Excel;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\AttendanceJob;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

// load paginator
// use Illuminate\Pagination\Paginator;

// load cursor pagination
// use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Log;
use Exception;

class AttendanceExcelReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|2|5,14|31', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request): View
	{
		if (!$request->id) {
			$bid = 1;
		} else {
			$bid = $request->id;
		}
		$batch = Bus::findBatch($bid);


		if (Storage::exists('public/excel/export.csv')) {
			$header[0] = [
							// '#',
							'Emp. No',
							'Staff Name',
							'Location',
							'Department',
							// 'Age',
							'Date Joined',
							'Date Confirmed',
							'Annual Leave Entitlement',
							'Utilize Annual Leave',
							'Balance Annual Leave',
							'MC Entitlement',
							'Utilize MC',
							'Balance MC',
							'Balance NRL',
							'Utilize UPL',
							'Utilize MC-UPL',
							'Absent',
							'Apparaisal Mark1',
							'Apparaisal Mark2',
							'Apparaisal Mark3',
							'Apparaisal Mark4',
							'Apparaisal Average Mark',
							'Late Frequency (0.5m per time)',
							'UPL Frequency (1day-5day=1m, 6day-10day=2m, >11day=3m)',
							'MC Frequency (9day-10day=1m, 11day-14day=2m, >15=3m)',
							'EL w/o Supporting Doc (0.5m per time)',
							'Absent w/o Notice or didn\'t Refill Form (1m per day)',
							'Absent As Reject By HR (1m per day)',
							'Apply Leave 3 Days Not In Advance (0.5m per time)',
							'UPL (Quarantine)',
							'Verbal Warning (1m per time)',
							'Warning Letter Frequency (3-5m per time)'
						];

			// (A) READ EXISTING CSV FILE INTO ARRAY
			$csv = fopen(storage_path('app/public/excel/export.csv'), 'r');
			while (($r=fgetcsv($csv)) !== false) {
				$rows[] = $r;
			}
			fclose($csv);

			// (B) PREPEND NEW ROWS
			$rows = array_merge($header, $rows);
			// dd($rows);

			// (C) SAVE UPDATED CSV
			// $csv = fopen(storage_path('app/public/excel/export.csv'), 'w');
			$filename = 'Staff_Appraisal_'.now()->format('j F Y g.i').'.csv';
			$file = fopen(storage_path('app/public/excel/'.$filename), 'w');
			foreach ($rows as $r) {
				fputcsv($file, $r);
			}
			fclose($file);
			Storage::delete('public/excel/export.csv');
			$url = Storage::url('public/excel/'.$filename);
			// return redirect($url);
			return Storage::download('public/excel/'.$filename);
		}

		return view('humanresources.hrdept.attendance.attendanceexcelreport.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		$validated = $request->validate(
			[
				'from' => 'required|date_format:Y-m-d|before_or_equal:to',
				'to' => 'required|date_format:Y-m-d|after_or_equal:from',
			],
			[
				'from.required' => 'Please insert date from',
				'to.required' => 'Please insert date to',
			],
			[
				'from' => 'From Date',
				'to' => 'To Date',
			]
		);
		$from = Carbon::parse($request->from);
		$to = Carbon::parse($request->to);

		$period = $from->daysUntil($to, 6);
		$dates = [];

		foreach ($period as $k => $v) {
			$dates[] = [
				'from' => $v->format('Y-m-d'),
				'to' => $v->format('Y-m-d')
			];
		}


		$dateChunks = array_chunk($dates, 4);

		dd($dates, $dateChunks);

		foreach ($dateChunks as $index => $values) {
			// $data[$index] = $values;
			foreach ($values as $value) {
				$data[$index][] = $value;
			}
			// dd($data[$index]);
			$dat[] = new AttendanceJob($data[$index], $request->only(['from', 'to']));
		}

		$batch = Bus::batch($dat)
					->name('Staff Attendance Payslip Process on -> '.now()->format('j M Y'))
					->progress(function (Batch $batch) {
						// A single job has completed successfully...
					})
					->then(function (Batch $batch) {
						// All jobs completed successfully...
					})
					->catch(function (Batch $batch, Throwable $e) {
						// First batch job failure detected...
					})
					->finally(function (Batch $batch) {
						// The batch has finished executing...
					})
					->dispatch();

		// Session(['lastBatchId' => $batch->id]);
		// return Excel::download(new PayslipExport($request->only(['from', 'to'])), $from.' - '.$to.' AttendancePayRoll.xlsx');
		// return redirect()->route('excelreport.create', ['id' => $batch->id]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $hRAttendance): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $hRAttendance): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRAttendance $hRAttendance): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $hRAttendance): JsonResponse
	{
		//
	}
}
