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
use App\Models\Login;

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

// load pdf
use Barryvdh\DomPDF\Facade\Pdf;

use Session;

class AttendanceReportPDFController extends Controller
{
  function __construct()
  {
    $this->middleware(['auth']);
    $this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['store']]);
  }

  public function store(Request $request)
  {
    // dd($request->all());
    $sa1 = HRAttendance::select('staff_id')
      ->whereIn('staff_id', $request->staff_id)
      ->where(function (Builder $query) use ($request) {
        $query->whereDate('attend_date', '>=', $request->from)
          ->whereDate('attend_date', '<=', $request->to);
      })
      ->groupBy('hr_attendances.staff_id')
      ->get();

    foreach ($sa1 as $k) {
      $lp[] = $k->staff_id;
    }

    $sa = Login::whereIn('staff_id', $lp)
      ->groupBy('staff_id')
      // ->orderBy('active', 'desc')
      ->orderBy('username')
      ->get();

    $pdf = PDF::loadView('humanresources.hrdept.attendance.attendancereport.storepdf', ['sa' => $sa, 'request' => $request]);
    // return $pdf->download('attendance monthly report ' . $request->from . ' - ' . $request->to . '.pdf');
    return $pdf->stream();
  }
}
