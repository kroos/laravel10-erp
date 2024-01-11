<?php

namespace App\Http\Controllers\HumanResources\HRDept;

// for controller output
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

// MODELS
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRTempPunchTime;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRRestdayCalendar;
use App\Models\HumanResources\OptWorkingHour;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// TIME & DATE
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

// IMPORT FROM EXCEL INTO DATABASE
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\AttendancePopulateJob;

use Illuminate\Support\Facades\Storage;

use Session;

class AttendanceUploadController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	* Display a listing of the resource.
	*/
	public function index()
	{
	//
	}

	/**
	* Show the form for creating a new resource.
	*/
	public function create(Request $request): View
	{
		if (!$request->id) {
			if (session()->exists('lastBatchIdAttPop')) {
				$bid = session()->get('lastBatchIdAttPop');
			} else {
				$bid = 1;
			}
		} else {
			$bid = $request->id;
		}
		$batch = Bus::findBatch($bid);
		return view('humanresources.hrdept.attendance.attendanceupload.create', ['batch' => $batch]);
	}

	/**
	* Store a newly created resource in storage.
	*/
	public function store(Request $request)/*: JsonResponse*/
	{
		// $validated = $request->validate(
		// 	[
		// 		'softcopy' => 'required|file|max:5120|mimes:xls,csv,xlsx',
		// 	],
		// 	[
		// 		// 'softcopy.required' => 'Please insert year',
		// 	],
		// 	[
		// 		'softcopy' => 'Excel File',
		// 	]
		// );
		if (!$request->file('softcopy')) {
			return redirect()->route('attendanceupload.create')->with('flash_message', 'No Excel File.');
			exit;
		}
		ini_set('max_execution_time', '0');
		HRTempPunchTime::truncate();

		if ($request->file('softcopy')) {
			// UPLOAD SOFTCOPY AND DATA EXCEL INTO DATABASE
			$fileName = $request->file('softcopy')->getClientOriginalName();
			$currentDate = Carbon::now()->format('Y-m-d His');
			$file = $currentDate . '_' . $fileName;
			$request->file('softcopy')->storeAs('public/attendance', $file);
			Excel::import(new AttendanceImport, $request->file('softcopy'));
		}

		// FETCH ACTIVE STAFF USER INFO
		$query_Recordset1 = Staff::where('active', 1)
										->whereNotIn('staffs.id', [61,62])
										// ->ddRawSql();
										->get();

		// LOOP ALL ACTIVE STAFF INFO INTO ARRAY
		foreach ($query_Recordset1 as $row_Recordset1) {
			$staff = [
				'username' => $row_Recordset1->hasmanylogin()->where('active', 1)->first()?->username,
				'staff_id' => $row_Recordset1->id,
				'name' => $row_Recordset1->name,
				'restday_group_id' => $row_Recordset1->restday_group_id,
				'wh_group_id' => $row_Recordset1->belongstomanydepartment()?->first()?->wh_group_id,
			];
			$staffs[] = $staff;
		}

		// GET THE LATEST ATTENDANCE RECORD DATE IN FACESCAN
		// $query_Recordset3 = DB::select('SELECT DATE(`hr_temp_punch_time`.Att_Time) AS LastDate FROM `hr_temp_punch_time` GROUP BY DATE(`hr_temp_punch_time`.Att_Time) ORDER BY LastDate ASC LIMIT 1');
		$row_Recordset3 = HRTempPunchTime::selectRaw('DATE(`hr_temp_punch_time`.Att_Time) AS LastDate')->groupByRaw('DATE(Att_Time)')->orderBy('Att_Time')->first()->LastDate;
		// $row_Recordset3 = $query_Recordset3[0]->LastDate;

		// GET THE LATEST ATTENDANCE RECORD DATE IN FACESCAN
		// $query_Recordset5 = DB::select('SELECT DATE(`hr_temp_punch_time`.Att_Time) AS CurrentDate FROM `hr_temp_punch_time` GROUP BY DATE(`hr_temp_punch_time`.Att_Time) ORDER BY CurrentDate DESC LIMIT 1');
		$row_Recordset5 = HRTempPunchTime::selectRaw('DATE(`hr_temp_punch_time`.Att_Time) AS CurrentDate')->groupByRaw('Att_Time')->orderBy('Att_Time', 'DESC')->first()->CurrentDate;
		// $row_Recordset5 = $query_Recordset5[0]->CurrentDate;
		// dd($row_Recordset3, $row_Recordset5);

		$data = array_chunk($staffs, 2);
		foreach ($data as $index => $values) {
			// $datacsv[$index] = $values;
			foreach ($values as $dataval) {
				// combine header with data and pickup column that we need
				$datacsv[$index][] = $dataval;
			}
			// dd($datacsv[$index]);

			// call queues by chunk
			// AttendancePopulateJob::dispatch($datacsv[$index]);

			// we need a progress so we use batch n comment out the queue above
			$dat[] = new AttendancePopulateJob($datacsv[$index], $row_Recordset3, $row_Recordset5);
		}
		// dd(new AttendancePopulateJob($datacsv[$index], $row_Recordset3, $row_Recordset5), $dat);

		$batches = Bus::batch($dat)
					->name('Attendance Populate Job on -> '.now()->format('j F Y g:i:s a'))
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

		Session(['lastBatchIdAttPop' => $batches->id]);
		// return redirect()->route('attendanceupload.create', ['id' => $batches->id])->with('flash_message', 'Successfully upload excel.');
		return response()->json(route('attendanceupload.create', ['id' => $batches->id]));
	}

	/**
	* Display the specified resource.
	*/
	public function show()
	{
	//
	}

	/**
	* Show the form for editing the specified resource.
	*/
	public function edit()
	{
	//
	}

	/**
	* Update the specified resource in storage.
	*/
	public function update()
	{
	//
	}

	/**
	* Remove the specified resource from storage.
	*/
	public function destroy()
	{
	//
	}
}
