<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRTempPunchTime;
use App\Models\Staff;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

use Session;

class AttendanceUploadController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,14', ['only' => ['index', 'show']]);
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
	public function create(): View
	{
		return view('humanresources.hrdept.attendance.attendanceupload.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		HRTempPunchTime::truncate();

		if ($request->file('softcopy')) {
			// UPLOAD SOFTCOPY AND DATA EXCEL INTO DATABASE
			$fileName = $request->file('softcopy')->getClientOriginalName();
			$currentDate = Carbon::now()->format('Y-m-d His');
			$file = $currentDate . '_' . $fileName;
			$request->file('softcopy')->storeAs('public/attendance', $file);
			Excel::import(new AttendanceImport, $request->file('softcopy'));
		} 

		Session::flash('flash_message', 'Successfully upload excel.');
		return redirect()->route('attendance.index');
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
