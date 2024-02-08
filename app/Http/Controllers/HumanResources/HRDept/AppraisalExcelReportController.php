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
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\StaffAppraisalExport;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRAppraisalSetting;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\AppraisalJob;

// load helper
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
use Exception;
use Log;

class AppraisalExcelReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
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
		if (!$request->id) {
			if (session()->exists('lastBatchId')) {
				$bid = session()->get('lastBatchId');
			} else {
				$bid = 1;
			}
		} else {
			$bid = $request->id;
		}
		$batch = Bus::findBatch($bid);

		// dd(Storage::exists('public/excel/export.csv'));
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
				'Late Frequency ('.HRAppraisalSetting::find(1)->value1.'m per time)',
				'UPL Frequency (1day-5day='.HRAppraisalSetting::find(2)->value1.'m, 6day-10day='.HRAppraisalSetting::find(2)->value2.'m, >11day='.HRAppraisalSetting::find(2)->value3.'m)',
				'MC Frequency (9day-10day='.HRAppraisalSetting::find(3)->value1.'m, 11day-14day='.HRAppraisalSetting::find(3)->value2.'m, >15='.HRAppraisalSetting::find(3)->value3.'m)',
				'EL w/o Supporting Doc ('.HRAppraisalSetting::find(4)->value1.'m per time)',
				'Absent w/o Notice or didn\'t Refill Form ('.HRAppraisalSetting::find(5)->value1.'m per day)',
				'Absent As Reject By HR ('.HRAppraisalSetting::find(6)->value1.'m per day)',
				'Apply Leave 3 Days Not In Advance ('.HRAppraisalSetting::find(7)->value1.'m per time)',
				'UPL (Quarantine)',
				'Verbal Warning ('.HRAppraisalSetting::find(8)->value1.'m per time)',
				'Warning Letter Frequency ('.HRAppraisalSetting::find(9)->value1.'m per time)'
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
			$filename = 'Staff_Appraisal_'.now()->format('j_F_Y_g.i').'.csv';
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
		return view('humanresources.hrdept.appraisal.report.create', ['batch' => $batch]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): JsonResponse
	{
		$validated = $request->validate(
			[
				'year' => 'required|integer|gte:2023',
			],
			[
				'year.required' => 'Please insert year',
			],
			[
				'year' => 'Apraisal Staff Year',
			]
		);

		$staffs = Staff::where('active', 1)->get();
		$year = $request->year;

		$dataprocess = $staffs->chunk(5);
		// process collection
		// $batch = Bus::batch([])->name('Staff Appraisal Process on -> '.now())->dispatch();
		foreach ($dataprocess as $index => $values) {
			// $data[$index] = $values;
			foreach ($values as $value) {
				$data[$index][] = $value;
			}
			// dd($data[$index]);
			// call queues by chunk
			// AppraisalJob::dispatch($dataprocess[$index]);
			// we need a progress so we use batch n comment out the queue above
			// $batch->add(new AppraisalJob($data[$index], $year));
			$dat[] = new AppraisalJob($data[$index], $year);
		}
		$batch = Bus::batch($dat)
					->name('Staff Appraisal Process on -> '.now()->format('j M Y'))
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

		Session(['lastBatchId' => $batch->id]);
		return response()->json(route('appraisalexcelreport.create', ['id' => $batch->id]));
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $staff): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $staff): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Staff $staff): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Staff $staff): JsonResponse
	{
		//
	}
}
