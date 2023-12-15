<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

// models
use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load queues
use App\Jobs\AttendanceProcessJob;

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

class AttendancesProcess extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'attendancesprocess';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update attendances with leave, outstation and overtime.';

	/**
	 * Execute the console command.
	 */
	public function handle()
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
		$batch = Bus::batch([])->name('Attendance Process on -> '.now())->dispatch();
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
			$batch->add(new AttendanceProcessJob($data[$index]));
		}
	}
}
