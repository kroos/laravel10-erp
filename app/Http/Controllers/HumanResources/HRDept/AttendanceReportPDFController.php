<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load validation
use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\Staff;

// load paginator
use Illuminate\Pagination\Paginator;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AttendanceReportPDFController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['store']]);
	}

	public function store(Request $request): View
	{
		// dd($request->all());
		$sa = HRAttendance::select('staff_id')
					->whereIn('staff_id', $request->staff_id)
					// ->whereIn('staff_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 52, 53, 54, 55, 56, 57, 58, 59, 65, 67, 68, 69, 71])
					->where(function (Builder $query) use ($request){
						$query->whereDate('attend_date', '>=', $request->from)
						// $query->whereDate('attend_date', '>=', '2023-11-01')
						->whereDate('attend_date', '<=', $request->to);
						// ->whereDate('attend_date', '<=', '2023-11-14');
					})
					->groupBy('hr_attendances.staff_id')
					->get();

		return view('humanresources.hrdept.attendance.attendancereport.storepdf', ['sa' => $sa, 'request' => $request]);
	}

}
