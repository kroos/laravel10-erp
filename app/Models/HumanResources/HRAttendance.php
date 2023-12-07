<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

// db relation class to load
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRAttendance extends Model
{
	use HasFactory, SoftDeletes;

	// protected $connection = 'mysql';
	protected $table = 'hr_attendances';

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstoleave(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HRLeave::class, 'leave_id');
	}

	public function belongstoopttcms(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptTcms::class, 'attendance_type_id');
	}

	public function belongstodaytype(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptDayType::class, 'daytype_id');
	}

	public function belongstooutstation(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HROutstation::class, 'outstation_id');
	}

	public function belongstoovertime(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HROvertime::class, 'overtime_id');
	}
}
