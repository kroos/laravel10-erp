<?php
namespace App\Helpers;

use App\Models\Staff;
use App\Models\Setting;

// use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder;

// load helper
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class CalculateMonth
{
	public function __construct()
	{
		$this->middleware(['auth']);
	}

	public static function unknown() {

	}
}
