<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;

// load models
use App\Models\Staff;
use App\Models\HumanResources\HREmergency;

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

    foreach ($request->emer as $value) {
      $HREmergency = HREmergency::findOrFail($value['id']);
      $HREmergency->contact_person = $value['contact_person'];
      $HREmergency->phone = $value['phone'];
      $HREmergency->address = $value['address'];
      $HREmergency->relationship_id = $value['relationship_id'];
      $HREmergency->update();
    }

    Session::flash('flash_message', 'Data successfully updated!');
    return Redirect::route('profile.show', $profile);
  }

  /**
   * Remove the specified resource from storage.
   */
  // public function destroy(Staff $staff)
  // {
  //     //
  // }
}
