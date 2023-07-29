<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class OptWorkingHour extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'option_working_hours';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanydepartment(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\DepartmentPivot::class, 'wh_group_id');
	}
}

