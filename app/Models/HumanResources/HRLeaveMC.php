<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRLeaveMC extends Model
{
	use HasFactory, SoftDeletes;

	// protected $connection = 'mysql';
	protected $table = 'hr_leave_mc';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	public function belongstomanyleave(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRLeave::class, 'pivot_leave_mc', 'leave_mc_id', 'leave_id')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}
}

