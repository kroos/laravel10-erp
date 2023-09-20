<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

// models
use App\Models\HumanResources\HRLeaveReplacement;

// validation
use App\Http\Requests\HumanResources\ReplacementLeave\ReplacementRequestRequestStore;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Session;

class ReplacementLeaveController extends Controller
{
    function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccess:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

    /**
     * Display a listing of the resource.
     */
    public function index() : View
    {
        return view('humanresources.hrdept.rleave.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('humanresources.hrdept.rleave.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReplacementRequestRequestStore $request): RedirectResponse
    {
        $newRecord = HRLeaveReplacement::create($request->all());

        Session::flash('flash_message', 'Successfully Add Replacement Leave.');
        return redirect()->route('rleave.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(HRLeaveReplacement $rleave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HRLeaveReplacement $rleave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HRLeaveReplacement $rleave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HRLeaveReplacement $rleave)
    {
        //
    }
}
