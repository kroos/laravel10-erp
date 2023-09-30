<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HRStaffEmergency;
use App\Models\HumanResources\HRStaffSpouse;
use App\Models\HumanResources\HRStaffChildren;

// load validation
use App\Http\Requests\HumanResources\Profile\ProfileRequestUpdate;

use Session;

class ProfileController extends Controller
{

	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('profileaccess', ['only' => ['show', 'edit', 'update']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.profile.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): View
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Staff $profile): View
	{
		return view('humanresources.profile.show', compact('profile'));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $profile): View
	{
		return view('humanresources.profile.edit', compact('profile'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(ProfileRequestUpdate $request, Staff $profile): RedirectResponse
	{
		// return $request->emer;
		// return \Carbon\Carbon::parse($request->dob)->format('Y-m-d');

		$profile->update($request->only(['ic', 'mobile', 'email', 'address', 'dob', 'gender_id', 'nationality_id', 'race_id', 'religion_id', 'marital_status_id']));

		if ($request->emer) {
			foreach ($request->emer as $emer_value) {
				$HRStaffEmergency = HRStaffEmergency::updateOrCreate(
					[
						'id' => $emer_value['id']
					],
					[
						'staff_id' => $emer_value['staff_id'],
						'contact_person' => $emer_value['contact_person'],
						'phone' => $emer_value['phone'],
						'address' => $emer_value['address'],
						'relationship_id' => $emer_value['relationship_id'],
					]
				);
			}
		}

		if ($request->spou) {
			foreach ($request->spou as $spou_value) {
				$HRStaffSpouse = HRStaffSpouse::updateOrCreate(
					[
						'id' => $spou_value['id']
					],
					[
						'staff_id' => $spou_value['staff_id'],
						'spouse' => $spou_value['spouse'],
						'id_card_passport' => $spou_value['id_card_passport'],
						'phone' => $spou_value['phone'],
						'dob' => $spou_value['dob'],
						'profession' => $spou_value['profession'],
					]
				);
			}
		}

		if ($request->chil) {
			foreach ($request->chil as $chil_value) {
				$HRStaffChildren = HRStaffChildren::updateOrCreate(
					[
						'id' => $chil_value['id']
					],
					[
						'staff_id' => $chil_value['staff_id'],
						'children' => $chil_value['children'],
						'dob' => $chil_value['dob'],
						'gender_id' => $chil_value['gender_id'],
						'health_status_id' => $chil_value['health_status_id'],
						'education_level_id' => $chil_value['education_level_id'],
					]
				);
			}
		}

		Session::flash('flash_message', 'Data successfully updated!');
		return Redirect::route('profile.show', $profile);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, Staff $profile): JsonResponse
	{
		if ($request->table == 'emergency') {
			$HRStaffEmergency = HRStaffEmergency::destroy(
				[
					'id' => $profile['id']
				]
			);

			return response()->json([
				'status' => 'success',
				'message' => 'Your emergency contact has been deleted.',
			]);
		}


		if ($request->table == 'spouse') {
			$HRStaffSpouse = HRStaffSpouse::destroy(
				[
					'id' => $profile['id']
				]
			);

			return response()->json([
				'status' => 'success',
				'message' => 'Your spouse contact has been deleted.',
			]);
		}


		if ($request->table == 'children') {
			$HRStaffChildren = HRStaffChildren::destroy(
				[
					'id' => $profile['id']
				]
			);

			return response()->json([
				'status' => 'success',
				'message' => 'Your children contact has been deleted.',
			]);
		}
	}
}
