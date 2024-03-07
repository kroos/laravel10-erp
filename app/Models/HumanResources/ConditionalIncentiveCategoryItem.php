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

class ConditionalIncentiveCategoryItem extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'ci_category_items';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	// public function hasmanyleavereplacement(): HasMany
	// {
	// 	return $this->hasMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'leave_id');
	// }

	// public function hasmanyconditionalincentiveitem(): HasMany
	// {
	// 	return $this->hasMany(\App\Models\HumanResources\HRLeaveAmend::class, 'leave_id');
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	public function belongstomanystaff(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\Staff::class, 'pivot_staff_ci_category_item', 'cicategory_item_id', 'staff_id')->withPivot('id', 'created_at')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstocicategory(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\ConditionalIncentiveCategory::class, 'ci_category_id');
	}

}
