<?php

namespace App\Model\HumanResource\HRSettings;

use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class WorkingHour extends Model
{
	// protected $connection = 'mysql';
	protected $table = 'option_working_hours';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanydepartment(): HasMany
	{
		return $this->hasMany(HumanResources\DepartmentPivot::class, 'wh_group_id');
	}
}

