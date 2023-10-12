<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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

class HROvertime extends Model
{
	use HasFactory, SoftDeletes;

	// protected $connection = 'mysql';
	protected $table = 'hr_overtimes';
	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany

  /////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstoassignstaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'assign_staff_id');
	}

	public function belongstoovertimerange(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HROvertimeRange::class, 'overtime_range_id');
	}

	// public function belongstoattendance(): BelongsTo
	// {
	// 	return $this->belongsTo(\App\Models\HumanResources\HRAttendance::class, 'attendance_id');
	// }
}


