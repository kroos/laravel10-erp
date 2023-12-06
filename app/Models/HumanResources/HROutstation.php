<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HROutstation extends Model
{
	use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'hr_outstations';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasone relationship
	public function hasonehrattendance(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRAttendance::class, 'outstation_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasmanyoutstationattendance(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HROutstationAttendance::class, 'outstation_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstocustomer(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
	}
}
