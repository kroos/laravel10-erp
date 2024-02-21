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

class HRAppraisalMark extends Model
{
    use HasFactory, SoftDeletes;
    // protected $connection = 'mysql';
    protected $table = 'hr_appraisal_marks';

    ////////////////////////// BelongsTo //////////////////////////
    public function belongstosection(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HRAppraisalSection::class, 'section_id')->withDefault();
	}

    public function belongstosectionsub(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HRAppraisalSectionSub::class, 'section_sub_id')->withDefault();
	}

    public function belongstomainquestion(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HRAppraisalMainQuestion::class, 'main_question_id')->withDefault();
	}

    public function belongstoquestion(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HRAppraisalQuestion::class, 'question_id')->withDefault();
	}
}
