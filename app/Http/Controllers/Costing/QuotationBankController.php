<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotBank;

use Illuminate\Http\Request;

use Session;

class QuotationBankController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.bank.index');
	}

	public function create()
	{
		return view('quotation.bank.create');
	}

	public function store(Request $request)
	{
		QuotBank::create($request->only('bank'));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotBank.index'));
	}

	public function show(QuotBank $quotBank)
	{
	//
	}

	public function edit(QuotBank $quotBank)
	{
		return view('quotation.bank.edit', compact(['quotBank']));
	}

	public function update(Request $request, QuotBank $quotBank)
	{
		$quotBank->updated($request->only('bank'));
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotBank.index'));
	}

	public function destroy(QuotBank $quotBank)
	{
		// $quotBank->destroy();
		QuotBank::destroy($quotBank->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

