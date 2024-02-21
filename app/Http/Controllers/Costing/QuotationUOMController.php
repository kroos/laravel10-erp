<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotUOM;

use Illuminate\Http\Request;

use Session;

class QuotationUOMController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.uom.index');
	}

	public function create()
	{
		return view('quotation.uom.create');
	}

	public function store(Request $request)
	{
		QuotUOM::create($request->only('uom'));
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotUOM.index'));
	}

	public function show(QuotUOM $quotUOM)
	{
	//
	}

	public function edit(QuotUOM $quotUOM)
	{
		return view('quotation.uom.edit', compact('quotUOM'));
	}

	public function update(Request $request, QuotUOM $quotUOM)
	{
		$quotUOM->update($request->uom);
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotUOM.index'));
	}

	public function destroy(QuotUOM $quotUOM)
	{
		// $quotUOM->destroy();
		QuotUOM::destroy($quotUOM->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

