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
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AppraisalPivot extends Model
{
	use HasFactory, SoftDeletes;
	// protected $connection = 'mysql';
	protected $table = 'pivot_apoint_appraisals';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsTo
	public function belongstostaffevaluator(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'evaluator_id');
	}

	public function belongstostaffevaluatee(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'evaluatee_id');
	}

	public function belongstoappraisalcategory(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptAppraisalCategories::class, 'appraisal_category_id');
	}
}

