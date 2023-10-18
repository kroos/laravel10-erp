<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load model
use App\Models\Setting;

use App\Models\Staff;

// load helper
use App\Helpers\UnavailableDateTime;
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AjaxController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
	}

	//////////////////////////////////////////////////////////////////////////////////////////////

}
