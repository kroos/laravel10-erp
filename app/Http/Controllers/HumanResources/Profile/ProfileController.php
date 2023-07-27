<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\Staff;
use App\Models\HumanResources\DepartmentPivot;
use App\Models\HumanResources\OptGender;

class ProfileController extends Controller
{

	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('profileaccess');
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return view('humanresources.profile.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $profile)
	{
		return view('humanresources.profile.show', compact('profile'));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $profile)
	{
		$department = DepartmentPivot::all()->pluck('department','id')->sortKeys()->toArray();
		$gender = OptGender::all()->pluck('gender','id')->sortKeys()->toArray();

		return view('humanresources.profile.edit', compact('profile', 'department', 'gender'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Staff $profile)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	// public function destroy(Staff $staff)
	// {
	//     //
	// }
}
