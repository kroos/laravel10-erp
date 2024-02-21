<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;

// load model
use App\Model\QuotItem;

use Illuminate\Http\Request;

use Session;

class QuotationItemController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		return view('quotation.item.index');
	}

	public function create()
	{
		return view('quotation.item.create');
	}

	public function store(Request $request)
	{
		QuotItem::create( array_add( $request->only(['item', 'info', 'price', 'remarks']), 'active', 1 ) );
		Session::flash('flash_message', 'Data successfully stored!');
		return redirect(route('quotItem.index'));
	}

	public function show(QuotItem $quotItem)
	{
	//
	}

	public function edit(QuotItem $quotItem)
	{
		return view('quotation.item.edit', compact(['quotItem']));
	}

	public function update(Request $request, QuotItem $quotItem)
	{
		$quotItem->update($request->only(['item', 'info', 'price', 'remarks']));
		Session::flash('flash_message', 'Data successfully updated!');
		return redirect(route('quotItem.index'));
	}

	public function updateitem(Request $request, QuotItem $quotItem)
	{
		$quotItem->update($request->only(['active']));
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}

	public function destroy(QuotItem $quotItem)
	{
		// $quotItem->destroy();
		QuotItem::destroy($quotItem->id);
		return response()->json([
			'message' => 'Data deleted',
			'status' => 'success'
		]);
	}
}

