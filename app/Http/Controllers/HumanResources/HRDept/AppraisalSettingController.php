<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAppraisalSetting;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AppraisalSettingController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		// $this->middleware('highMgmtAccess:1|2|5,14|31')->only('index', 'show');
		$this->middleware('highMgmtAccessLevel1:1,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function create(): View
	{
		return view('humanresources.hrdept.appraisal.setting.create');
	}

	public function update(Request $request, HRAppraisalSetting $appraisalsetting): JsonResponse
	{
		// dd($request->all(), $request->except(['_token', 'id']));
		$appraisalsetting->update($request->except(['_token', 'id']));
		return response()->json(['message' => 'Success', 'status' => 'success']);
	}
}
