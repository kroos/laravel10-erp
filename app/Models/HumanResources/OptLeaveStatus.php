<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\Model;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OptLeaveStatus extends Model
{
	use HasFactory;
	// protected $connection = 'mysql';
	protected $table = 'option_leave_statuses';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship
	public function hasmanyleave(): HasMany
	{
		return $this->hasMany(HumanResources\HRLeave::class, 'leave_status_id');
	}

	public function hasmanyleaveapprovalbackup(): HasMany
	{
		return $this->hasMany(HumanResources\HRLeaveApprovalBackup::class, 'leave_status_id');
	}

	public function hasmanyleaveapprovalsupervisor(): HasMany
	{
		return $this->hasMany(HumanResources\HRLeaveApprovalSupervisor::class, 'leave_status_id');
	}

	public function hasmanyleaveapprovalhod(): HasMany
	{
		return $this->hasMany(HumanResources\HRLeaveApprovalHOD::class, 'leave_status_id');
	}

	public function hasmanyleaveapprovaldir(): HasMany
	{
		return $this->hasMany(HumanResources\HRLeaveApprovalDirector::class, 'leave_status_id');
	}

	public function hasmanyleaveapprovalhr(): HasMany
	{
		return $this->hasMany(HumanResources\HRLeaveApprovalHR::class, 'leave_status_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	// belongsto relationship

}
