<?php

namespace App\Jobs;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\HumanResources\HRLeaveReplacement;
use App\Models\HumanResources\HRDisciplinary;
use App\Models\HumanResources\OptBranch;
use App\Models\HumanResources\OptDepartment;

use Illuminate\Http\Request;

// load batch and queue
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

// load helper
use App\Helpers\UnavailableDateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load lib
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;
use Throwable;
use Log;
use Exception;

class StaffAppraisalJob implements ShouldQueue
{
	use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $dataprocess;

	/**
	 * Create a new job instance.
	 */
	public function __construct($dataprocess)
	{
		$this->dataprocess = $dataprocess;
		// dd($this->dataprocess);
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{

	}
}
