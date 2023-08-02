<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

// load models
use App\Models\Staff;

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
    return view('humanresources.profile.edit', compact('profile'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(ProfileRequestUpdate $request, Staff $profile): RedirectResponse
  {
    // return $request;
    // return \Carbon\Carbon::parse($request->dob)->format('Y-m-d');

    $userupdate = $profile->update($request->all());
    $emerupdate = $userupdate->hasmanyemergency()->get()->update($request->only(['contact_person', 'address', 'phone', 'relationship_id']));
    if ($emerupdate) {
      return 'success';
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
