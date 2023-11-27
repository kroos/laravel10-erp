<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// for controller output
// use Illuminate\Http\RedirectResponse;
// use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class HRDeptController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('highMgmtAccess:1|2|4|5,NULL'/*, ['only' => ['show', 'edit', 'update']]*/);
	}

	public function index(): View
	{
		return view('humanresources.hrdept.index');
	}
}
