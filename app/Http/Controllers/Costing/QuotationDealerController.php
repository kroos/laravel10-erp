<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotDealer;

use Illuminate\Http\Request;

use Session;

class QuotationDealerController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.dealer.index');
	}

	public function create()
	{
		return view('quotation.dealer.create');
	}

	public function store(Request $request)
	{
		QuotDealer::create($request->only('dealer'));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotDeal.index'));
	}

	public function show(QuotDealer $quotDeal)
	{
	//
	}

	public function edit(QuotDealer $quotDeal)
	{
		return view('quotation.dealer.edit', compact('quotDeal'));
	}

	public function update(Request $request, QuotDealer $quotDeal)
	{
		$quotDeal->update($request->only(['dealer']));
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotDeal.index'));
	}

	public function destroy(QuotDealer $quotDeal)
	{
		// $quotDeal->destroy();
		QuotDealer::destroy($quotDeal->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

