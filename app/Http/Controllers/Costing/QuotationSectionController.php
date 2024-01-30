<?php

namespace App\Http\Controllers\Sales\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotQuotationSection;

use Illuminate\Http\Request;

use Session;

class QuotationSectionController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
	}

	public function create()
	{
	}

	public function store(Request $request)
	{
	}

	public function show(QuotQuotationSection $quotSection)
	{
	//
	}

	public function edit(QuotQuotationSection $quotSection)
	{
	}

	public function update(Request $request, QuotQuotationSection $quotSection)
	{
	//
	}

	public function destroy(QuotQuotationSection $quotSection)
	{
		foreach($quotSection->hasmanyquotsectionitem()->get() as $sec) {
			$sec->hasmanyquotsectionitemattrib()->delete();
		}
		$quotSection->hasmanyquotsectionitem()->delete();
		// $quotSection->destroy();
		QuotQuotationSection::destroy($quotSection->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}
