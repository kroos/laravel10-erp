<?php
namespace App\Http\Controllers\HumanResources\HRDept;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

// models
use App\Models\Staff;
use App\Models\HumanResources\OptWeekDates;
// use App\Models\HumanResources\ConditionalIncentiveCategory;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

// load db facade
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ConditionalIncentiveJob;


// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use \Carbon\Carbon;
use \Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Log;
use Session;
use Exception;
use Throwable;

class ConditionalIncentiveStaffCheckingReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	// public function index(): View
	// {
	// }

	public function create(Request $request)//: View
	{
		if (!$request->id) {
			if (session()->exists('lastBatchIdPay')) {
				$bid = session()->get('lastBatchIdPay');
				$from1 = session()->get('date_from');
				$to1 = session()->get('date_to');
			} else {
				$bid = 1;
				session()->forget('date_from');
				session()->forget('date_to');
				$from1 = null;
				$to1 = null;
			}
		} else {
			$bid = $request->id;
			$from1 = null;
			$to1 = null;
		}
		$batch = Bus::findBatch($bid);
		$from = OptWeekDates::find($from1)?->week;
		$to = OptWeekDates::find($to1)?->week;

		if (Storage::exists('public/excel/cistaff.csv')) {
			$header[-1] = ['Staff', 'Incentive Description', 'Weeks', 'Incentive Deduction (RM)'];

			// (A) READ EXISTING CSV FILE INTO ARRAY
			$csv = fopen(storage_path('app/public/excel/cistaff.csv'), 'r');
			while (($r=fgetcsv($csv)) !== false) {
				$rows[] = $r;
			}
			fclose($csv);

			// (B) PREPEND NEW ROWS
			$rows = array_merge($header, $rows);
			// dd($rows);

			// (C) SAVE UPDATED CSV
			// $csv = fopen(storage_path('app/public/excel/payslip.csv'), 'w');
			$filename = 'Staff_Conditional_Incentive_'.$from.'_-_'.$to.'.csv';
			$file = fopen(storage_path('app/public/excel/'.$filename), 'w');
			foreach ($rows as $r) {
				fputcsv($file, $r);
			}
			fclose($file);
			Storage::delete('public/excel/cistaff.csv');
			$url = Storage::url('public/excel/'.$filename);
			// return redirect($url);
			session()->forget('date_from');
			session()->forget('date_to');
			return Storage::download('public/excel/'.$filename);
		}

		return view('humanresources.hrdept.conditionalincentive.staffcheckreport.create', ['batch' => $batch]);
		// return view('humanresources.hrdept.conditionalincentive.staffcheckreport.create');
	}

	public function store(Request $request)// : RedirectResponse
	{
		// dd($request->all());
		$validated = $request->validate(
			[
				'date_from' => 'required|lte:date_to',
				'date_to' => 'required|gte:date_from',
			],
			[
				'date_from.lte' => 'The :attribute field must be less than or equal to To Week.',
				'date_to.gte' => 'The :attribute field must be greater than or equal to From Week.',
			],
			[
				'date_from' => 'From Week',
				'date_to' => 'To Week',
			]
		);

		$cistaff = ConditionalIncentiveCategoryItem::all();
		$staf = [];
		foreach ($cistaff as $v) {
			foreach ($v->belongstomanystaff()->get() as $v1) {
				$staf[] = $v1->pivot->staff_id;
			}
		}
		$staffs = array_unique($staf);
		// $incentivestaffs = Staff::select('staffs.id', 'logins.username', 'staffs.name')->join('logins', 'staffs.id', '=', 'logins.staff_id')->orderBy('logins.username')->whereIn('staffs.id', $staffs)->where('logins.active', 1)->get();

		$stchunk = array_chunk($staffs, 2);
		// process collection
		// $batch = Bus::batch([])->name('Conditional Incentive Staff on -> '.now())->dispatch();
		foreach ($stchunk as $k1 => $v1) {
			// $data[$index] = $values;
			foreach ($v1 as $k2 => $v2) {
				$data[$k1][$k2] = $v2;
			}
		// 	// dd($data[$index]);
		// 	// $batch->add(new AttendancePayslipJob($data[$index], $year));
			$dat[$k1] = new ConditionalIncentiveJob($data[$k1], $request->only(['date_from', 'date_to']));
		}
		// dd($incentivestaffs, $staff, $stchunk, $data[$index]);

		$batch = Bus::batch($dat)
					->name('Conditional Incentive Staff on -> '.now()->format('j M Y'))
		// 			// ->progress(function (Batch $batch) {
		// 			// 	// A single job has completed successfully...
		// 			// })
		// 			// ->then(function (Batch $batch) {
		// 			// 	// All jobs completed successfully...
		// 			// })
		// 			// ->catch(function (Batch $batch, Throwable $e) {
		// 			// 	// First batch job failure detected...
		// 			// })
		// 			// ->finally(function (Batch $batch) {
		// 			// 	// The batch has finished executing...
		// 			// })
					->dispatch();
		session(['lastBatchIdPay' => $batch->id]);
		session(['date_from' => $request->date_from]);
		session(['date_to' => $request->date_to]);
		return redirect()->route('cicategorystaffcheckreport.create', ['id' => $batch->id, 'date_from' => $request->date_from, 'date_to' => $request->date_to]);
		// return redirect()->route('cicategorystaffcheckreport.create', ['incentivestaffs' => $incentivestaffs, 'date_from' => $request->date_from, 'date_to' => $request->date_to]);
	}

	// public function show(ConditionalIncentiveCategoryItem $cicategoryitem): View
	// {
	// 	//
	// }

	// public function edit(ConditionalIncentiveCategoryItem $cicategoryitem): View
	// {
	// 	//
	// }

	// public function update(Request $request, ConditionalIncentiveCategoryItem $cicategoryitem): RedirectResponse
	// {
	// 	//
	// }

	// public function destroy(Request $request, ConditionalIncentiveCategoryItem $cicategoryitem): JsonResponse
	// {
	// 	//
	// }
}
