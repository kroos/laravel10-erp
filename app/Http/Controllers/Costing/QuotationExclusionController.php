<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotExclusion;

use Illuminate\Http\Request;

use Session;

class QuotationExclusionController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.exclusion.index');
	}

	public function create()
	{
		return view('quotation.exclusion.create');
	}

	public function store(Request $request)
	{
		QuotExclusion::create($request->only(['exclusion']));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotExcl.index'));
	}

	public function show(QuotExclusion $quotExcl)
	{
	//
	}

	public function edit(QuotExclusion $quotExcl)
	{
		return view('quotation.exclusion.edit', compact(['quotExcl']));
	}

	public function update(Request $request, QuotExclusion $quotExcl)
	{
		$quotExcl->update($request->only(['exclusion']));
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotExcl.index'));
	}

	public function destroy(QuotExclusion $quotExcl)
	{
		// $quotExcl->destroy();
		QuotExclusion::destroy($quotExcl->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

