<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRAttendance extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'hr_attendances';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasmanyovertime(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveAmend::class, 'leave_id');
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany


	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstodaytype(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptDayType::class, 'daytype_id');
	}
}


