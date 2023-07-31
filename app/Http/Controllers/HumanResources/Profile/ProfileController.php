<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

// load models
use App\Models\Staff;
use App\Models\HumanResources\OptGender;
use App\Models\HumanResources\OptCountry;
use App\Models\HumanResources\OptReligion;
use App\Models\HumanResources\OptRace;
use App\Models\HumanResources\OptMaritalStatus;
use App\Models\HumanResources\OptRelationship;

// load validation
use App\Http\Requests\HumanResources\ProfileRequestUpdate;

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
    $gender = OptGender::all()->pluck('gender', 'id')->sortKeys()->toArray();
    $nationality = OptCountry::all()->pluck('country', 'id')->sortKeys()->toArray();
    $religion = OptReligion::all()->pluck('religion', 'id')->sortKeys()->toArray();
    $race = OptRace::all()->pluck('race', 'id')->sortKeys()->toArray();
    $marital_status = OptMaritalStatus::all()->pluck('marital_status', 'id')->sortKeys()->toArray();
    $relationship = OptRelationship::all()->pluck('relationship', 'id')->sortKeys()->toArray();
    $emergencies = $profile->hasmanyemergency()->get();
    $loop = 1;

    return view('humanresources.profile.edit', compact('profile', 'gender', 'nationality', 'religion', 'race', 'marital_status', 'relationship', 'emergencies', 'loop'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(ProfileRequestUpdate $request, Staff $profile)/*: RedirectResponse*/
  {
    return $request;

    $profile->update($request->all());

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
