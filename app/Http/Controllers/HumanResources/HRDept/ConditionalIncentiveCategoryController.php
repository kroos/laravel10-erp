<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

// models
use App\Models\Staff;
use App\Models\HumanResources\ConditionalIncentiveCategory;
use App\Models\HumanResources\ConditionalIncentiveCategoryItem;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use Session;
use Throwable;
use Exception;
use Log;

class ConditionalIncentiveCategoryController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function index(): View
	{
		$cicategories = ConditionalIncentiveCategory::all();
		return view('humanresources.hrdept.conditionalincentive.category.index', ['cicategories' => $cicategories]);
	}

	public function create(): View
	{
		return view('humanresources.hrdept.conditionalincentive.category.create');
	}

	public function store(Request $request): RedirectResponse
	{
	}

	public function show(ConditionalIncentiveCategory $cicategory): View
	{
	}

	public function edit(ConditionalIncentiveCategory $cicategory): View
	{
		return view('humanresources.hrdept.conditionalincentive.category.edit', ['cicategory' => $cicategory]);
	}

	public function update(Request $request, ConditionalIncentiveCategory $cicategory): RedirectResponse
	{
	}

	public function destroy(Request $request, ConditionalIncentiveCategory $cicategory): JsonResponse
	{
	}
}
