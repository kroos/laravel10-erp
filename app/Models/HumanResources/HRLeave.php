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

class HRLeave extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'hr_leaves';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasoneleaveapprovalbackup(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalBackup::class, 'staff_leave_id');
	}

	public function hasoneleaveapprovalsupervisor(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'staff_leave_id');
	}

	public function hasoneleaveapprovalhod(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalHOD::class, 'staff_leave_id');
	}

	public function hasoneleaveapprovaldir(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalDirector::class, 'staff_leave_id');
	}

	public function hasoneleaveapprovalhr(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalHR::class, 'staff_leave_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(Staff::class, 'staff_id');
	}

	public function belongstooptleave(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptLeaveType::class, 'leave_type_id');
	}
}


