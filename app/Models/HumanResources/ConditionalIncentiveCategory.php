<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConditionalIncentiveCategory extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'ci_categories';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	// public function hasmanyleavereplacement(): HasMany
	// {
	// 	return $this->hasMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'leave_id');
	// }

	public function hasmanycicategoryitem(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\ConditionalIncentiveCategoryItem::class, 'ci_category_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	// public function belongstomany(): BelongsToMany
	// {
	// 	return $this->belongsToMany(\App\Models\HumanResources\HRLeaveAnnual::class, 'pivot_leave_annuals', 'leave_id', 'leave_annual_id')->withTimestamps();
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	// public function belongstostaff(): BelongsTo
	// {
	// 	return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	// }

}


