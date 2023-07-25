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

class OptRestdayGroup extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'option_restday_groups';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasmanystaff(): HasMany
	{
		return $this->hasMany(Staff::class, 'restday_group_id');
	}

	public function hasmanyrestdaycalendar(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRRestdayCalendar::class, 'restday_group_id');
	}
}
