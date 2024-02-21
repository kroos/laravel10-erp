<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotWarranty;

use Illuminate\Http\Request;

use Session;

class QuotationWarrantyController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.warranty.index');
	}

	public function create()
	{
		return view('quotation.warranty.create');
	}

	public function store(Request $request)
	{
		QuotWarranty::create($request->only(['warranty']));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotWarr.index'));
	}

	public function show(QuotWarranty $quotWarr)
	{
	//
	}

	public function edit(QuotWarranty $quotWarr)
	{
		return view('quotation.warranty.edit', compact(['quotWarr']));
	}

	public function update(Request $request, QuotWarranty $quotWarr)
	{
		$quotWarr->update($request->only(['warranty']));
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotWarr.index'));
	}

	public function destroy(QuotWarranty $quotWarr)
	{
		// $quotWarr->destroy();
		QuotWarranty::destroy($quotWarr->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

