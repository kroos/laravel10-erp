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
		$attendance = HRAttendance::whereYear('attend_date', now()->format('Y'))
									->get();				// collection
									// ->toArray();			// array
		// dd($attendance);

		// if $attendance a collection
		$dataprocess = $attendance->chunk(1000);

		// if $attendance an array
		// $dataprocess = array_chunk($attendance, 1000);

		// dd($dataprocess);

		// $batch = Bus::batch( new AttendanceProcess($dataprocess) )->name('Process on -> '.now())->dispatch();
		$batch = Bus::batch([])->name('Process on -> '.now())->dispatch();
		// process collection
		foreach ($dataprocess as $index => $values) {
			// $data[$index][] = $values;
			foreach ($values as $value) {
				$data[$index][] = $value;
			}
			// dd($data[$index]);

			// call queues by chunk
			// AttendanceProcess::dispatch($dataprocess[$index]);

			// we need a progress so we use batch n comment out the queue above
			$batch->add(new AttendanceProcess($data[$index]));
		}
		Session(['lastBatchId' => $batch->id]);
		return response()->json(route('attendanceprocess.index', ['id' => $batch->id]));

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
