<?php
namespace App\Http\Controllers\HumanResources\HRDept;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// models
use App\Models\Staff;
use App\Models\HumanResources\OptWeekDates;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;
// use App\Models\HumanResources\ConditionalIncentiveCategory;

// load db facade
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\DB;

// for controller output
use Illuminate\View\View;
// use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

// load array helper
// use Illuminate\Support\Arr;
// use Illuminate\Support\Str;
// use Illuminate\Support\Collection;
// use Illuminate\Support\Facades\Http;

// use \Carbon\Carbon;
// use Carbon\CarbonImmutable;
// use Session;
// use Throwable;
// use Exception;
// use Log;

class ConditionalIncentiveStaffCheckingReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function index(): View
	{
		return view('humanresources.hrdept.conditionalincentive.staffcheckreport.index');
	}

	// public function create(): View
	// {
	// 	//
	// }

	// public function store(Request $request): RedirectResponse
	// {
	// 	//
	// }

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
