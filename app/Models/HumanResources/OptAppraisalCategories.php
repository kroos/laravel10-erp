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

class OptAppraisalCategories extends Model
{
    use HasFactory, SoftDeletes;
	// protected $connection = 'mysql';
	protected $table = 'option_appraisal_categories';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function belongstomanysection(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRAppraisalSection::class, 'pivot_category_appraisals', 'category_id', 'section_id')->withPivot('version', 'sort')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanystaff(): HasMany
	{
		return $this->hasMany(\App\Models\Staff::class, 'appraisal_category_id');
	}
}
