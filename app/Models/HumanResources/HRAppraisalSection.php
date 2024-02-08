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

class HRAppraisalSection extends Model
{
    use HasFactory, SoftDeletes;
    // protected $connection = 'mysql';
    protected $table = 'hr_appraisal_sections';

    ////////////////////////// belongsToMany //////////////////////////
    public function belongstomanycategorypivot(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\OptAppraisalCategories::class, 'pivot_category_appraisals', 'section_id', 'category_id')->withPivot('version', 'sort')->withTimestamps();
	}
}
