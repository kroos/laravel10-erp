<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// models
// use App\Models\Staff;
// use App\Models\HumanResources\ConditionalIncentiveCategory;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

// load db facade
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\DB;

// for controller output
use Illuminate\Http\RedirectResponse;
// use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;


// load array helper
// use Illuminate\Support\Arr;
// use Illuminate\Support\Str;
// use Illuminate\Support\Collection;
// use Illuminate\Support\Facades\Http;

// use Session;
// use Throwable;
// use Exception;
// use Log;

class ConditionalIncentiveCategoryItemController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function index(): View
	{
		return view('humanresources.hrdept.conditionalincentive.categoryitem.index', ['cicategoryitem' => $cicategoryitem]);
	}

	public function create(): View
	{
		return view('humanresources.hrdept.conditionalincentive.categoryitem.create');
	}

	public function store(Request $request): RedirectResponse
	{
	}

	public function show(ConditionalIncentiveCategoryItem $cicategoryitem): View
	{
	}

	public function edit(ConditionalIncentiveCategoryItem $cicategoryitem): View
	{
		return view('humanresources.hrdept.conditionalincentive.categoryitem.edit', ['cicategoryitem' => $cicategoryitem]);
	}

	public function update(Request $request, ConditionalIncentiveCategoryItem $cicategoryitem): RedirectResponse
	{
	}

	public function destroy(Request $request, ConditionalIncentiveCategoryItem $cicategoryitem): JsonResponse
	{
	}
}
