<?php
namespace App\Http\Controllers\HumanResources\Process;

use App\Http\Controllers\Controller;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// models
use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load queues
use App\Jobs\AttendanceProcess;

// load batch and queue
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Exception;
use Log;

class AttendanceProcessController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		try {
			$attendance = HRAttendance::where(function(Builder $query) {
					$query->whereDate('attend_date', '>=', now()->startOfYear())
					->whereDate('attend_date', '<=', now()->endOfYear());
				})
				->get();				// collection
				// ->toArray();			// array
			// dd($attendance, now()->startOfYear(), now()->endOfYear());

			// if $attendance a collection
			$dataprocess = $attendance->chunk(1000);

			// if $attendance an array
			// $data = array_chunk($attendance, 1000);

			// dd($data);

			$batch = Bus::batch( new AttendanceProcess($dataprocess) )->name('Process on -> '.now())->dispatch();
			// process collection
			// foreach ($data as $index => $values) {
			// 	$dataprocess[$index][] = ['id' => $values['id'], 'staff_id' => $values['staff_id'], 'daytype_id' => $values['daytype_id'], 'attendance_type_id' => $values['attendance_type_id'], 'attend_date' => $values['attend_date'], 'in' => $values['in'], 'break' => $values['break'], 'resume' => $values['resume'], 'out' => $values['out'], 'time_work_hour' => $values['time_work_hour'], 'work_hour' => $values['work_hour'], 'overtime_id' => $values['overtime_id'], 'leave_id' => $values['leave_id'], 'outstation_id' => $values['outstation_id'], 'remarks' => $values['remarks'], 'hr_remarks' => $values['hr_remarks'], 'exception' => $values['exception'], 'created_at' => $values['created_at'], 'updated_at' => $values['updated_at'], 'deleted_at' => $values['deleted_at']];
			// 	 // = ['id', 'staff_id', 'daytype_id', 'attendance_type_id', 'attend_date', 'in', 'break', 'resume', 'out', 'time_work_hour', 'work_hour', 'overtime_id', 'leave_id', 'outstation_id', 'remarks', 'hr_remarks', 'exception', 'created_at', 'updated_at', 'deleted_at'];
			// 	dd($dataprocess[$index]);

			// 	// call queues by chunk
			// 	// AttendanceProcess::dispatch($dataprocess[$index]);

			// 	// we need a progress so we use batch n comment out the queue above
			// 	$batch->add(new AttendanceProcess($dataprocess[$index]));
			// }
		} catch (Exception $e) {
			return $e;
		}
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $hRAttendance)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $hRAttendance)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRAttendance $hRAttendance)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $hRAttendance)
	{
		//
	}
}
