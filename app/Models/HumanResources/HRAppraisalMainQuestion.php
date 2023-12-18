<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRAppraisalMainQuestion extends Model
{
    use HasFactory;
    // protected $connection = 'mysql';
    protected $table = 'hr_appraisal_main_questions';

    /////////////////////////////////////////////////////////////////////////////////////////
    // belongsto relationship
    public function belongstohrappraisalsectionsub(): BelongsTo
    {
        return $this->belongsTo(\App\Models\HumanResources\HRAppraisalSectionSub::class, 'section_sub_id')->withDefault();
    }
}
