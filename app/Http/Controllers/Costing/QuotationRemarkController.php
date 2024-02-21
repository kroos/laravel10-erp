<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotRemark;

use Illuminate\Http\Request;

use Session;

class QuotationRemarkController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.remarks.index');
	}

	public function create()
	{
		return view('quotation.remarks.create');
	}

	public function store(Request $request)
	{
		QuotRemark::create( $request->only('quot_remarks') );
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotRem.index'));
	}

	public function show(QuotRemark $quotRem)
	{
	//
	}

	public function edit(QuotRemark $quotRem)
	{
		return view('quotation.remarks.edit', compact('quotRem'));
	}

	public function update(Request $request, QuotRemark $quotRem)
	{
		$quotRem->update( $request->only('quot_remarks') );
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotRem.index'));
	}

	public function destroy(QuotRemark $quotRem)
	{
		// $quotRem->destroy();
		QuotRemark::destroy($quotRem->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

