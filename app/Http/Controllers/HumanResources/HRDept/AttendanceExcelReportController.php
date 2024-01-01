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
use App\Jobs\AttendancePayslipJob;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

use App\Models\Login;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HROvertimeRange;

// load helper
use App\Helpers\TimeCalculator;
use App\Helpers\UnavailableDateTime;

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
	public function create(Request $request)/*: View*/
	{
		$from = Carbon::parse(session()->get('from'))->format('j_M_Y');
		$to = Carbon::parse(session()->get('to'))->format('j_M_Y');
		if (!$request->id) {
			if (session()->exists('lastBatchIdPay')) {
				$bid = session()->get('lastBatchIdPay');
			} else {
				$bid = 1;
			}
		} else {
			$bid = $request->id;
		}
		$batch = Bus::findBatch($bid);

		if (Storage::exists('public/excel/payslip.csv')) {

			$header[-1] = ['Emp No', 'Name', 'AL', 'NRL', 'MC', 'UPL', 'Absent', 'UPMC', 'Lateness(minute)', 'Early Out(minute)', 'No Pay Hour', 'Maternity', 'Hospitalization', 'Other Leave', 'Compassionate Leave', 'Marriage Leave', 'Day Work', '1.0 OT', '1.5 OT', 'OT', 'TF'];

			// (A) READ EXISTING CSV FILE INTO ARRAY
			$csv = fopen(storage_path('app/public/excel/payslip.csv'), 'r');
			while (($r=fgetcsv($csv)) !== false) {
				$rows[] = $r;
			}
			fclose($csv);

			// (B) PREPEND NEW ROWS
			$rows = array_merge($header, $rows);
			// dd($rows);

			// (C) SAVE UPDATED CSV
			// $csv = fopen(storage_path('app/public/excel/payslip.csv'), 'w');
			$filename = 'Staff_Attendance_Payslip_'.$from.'_-_'.$to.'.csv';
			$file = fopen(storage_path('app/public/excel/'.$filename), 'w');
			foreach ($rows as $r) {
				fputcsv($file, $r);
			}
			fclose($file);
			Storage::delete('public/excel/payslip.csv');
			$url = Storage::url('public/excel/'.$filename);
			// return redirect($url);
			session()->forget('from');
			session()->forget('to');
			return Storage::download('public/excel/'.$filename);
		}
		return view('humanresources.hrdept.attendance.attendanceexcelreport.create', ['batch' => $batch]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)/*: RedirectResponse*/
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

		$from = Carbon::parse($request->from)->format('j F Y');
		$to = Carbon::parse($request->to)->format('j F Y');

		// get staff which is in attendance for a particular date
		$hratt = HRAttendance::select('staff_id')
				->where(function (Builder $query) use ($request) {
					$query->whereDate('attend_date', '>=', $request->from)
						->whereDate('attend_date', '<=', $request->to);
				})
				->groupBy('staff_id')
				// ->ddrawsql();
				->get();
		foreach ($hratt as $v) {
			$staff[] = ['staff_id' => $v->staff_id];
		}

		// $dataprocess = $hratt->chunk(5);

		$stchunk = array_chunk($staff, 5);


		// process collection
		// $batch = Bus::batch([])->name('Staff Appraisal Process on -> '.now())->dispatch();
		foreach ($stchunk as $index => $values) {
			// $data[$index] = $values;
			foreach ($values as $value) {
				$data[$index][] = $value;
			}
			// dd($data[$index]);
			// $batch->add(new AttendancePayslipJob($data[$index], $year));
			$dat[] = new AttendancePayslipJob($data[$index], $request->only(['from', 'to']));
		}
		// dd($hratt, $staff, $stchunk, $data[$index]);

		$batch = Bus::batch($dat)
					->name('Staff Attendances Payslip on -> '.now()->format('j M Y'))
					// ->progress(function (Batch $batch) {
					// 	// A single job has completed successfully...
					// })
					// ->then(function (Batch $batch) {
					// 	// All jobs completed successfully...
					// })
					// ->catch(function (Batch $batch, Throwable $e) {
					// 	// First batch job failure detected...
					// })
					// ->finally(function (Batch $batch) {
					// 	// The batch has finished executing...
					// })
					->dispatch();
		session(['lastBatchIdPay' => $batch->id]);
		session(['from' => $request->from]);
		session(['to' => $request->to]);
		// return Excel::download(new PayslipExport($request->only(['from', 'to'])), $from.' - '.$to.' AttendancePayRoll.xlsx');
		return redirect()->route('excelreport.create', ['id' => $batch->id]);
		// return redirect()->route('excelreport.create');
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
