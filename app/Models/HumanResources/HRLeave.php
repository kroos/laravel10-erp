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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HRLeave extends Model
{
	use HasFactory;

	// protected $connection = 'mysql';
	protected $table = 'hr_leaves';

	/////////////////////////////////////////////////////////////////////////////////////////
	// hasmany relationship

	// public function hasmanyleavereplacement(): HasMany
	// {
	// 	return $this->hasMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'leave_id');
	// }

	public function hasmanyleaveamend(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveAmend::class, 'leave_id');
	}

	public function hasmanyleaveapprovalbackup(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalBackup::class, 'leave_id');
	}

	public function hasmanyleaveapprovalsupervisor(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'leave_id');
	}

	public function hasmanyleaveapprovalhod(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalHOD::class, 'leave_id');
	}

	public function hasmanyleaveapprovaldir(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalDirector::class, 'leave_id');
	}

	public function hasmanyleaveapprovalhr(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalHR::class, 'leave_id');
	}

	public function hasmanyattendance(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRAttendance::class, 'leave_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	public function belongstomanyleaveannual(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRLeaveAnnual::class, 'pivot_leave_annuals', 'leave_id', 'leave_annual_id')->withTimestamps();
	}

	public function belongstomanyleavemc(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRLeaveMC::class, 'pivot_leave_mc', 'leave_id', 'leave_mc_id')->withTimestamps();
	}

	public function belongstomanyleavematernity(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRLeaveMaternity::class, 'pivot_leave_maternities', 'leave_id', 'leave_maternity_id')->withTimestamps();
	}

	public function belongstomanyleavereplacement(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'pivot_leave_replacements', 'leave_id', 'leave_replacement_id')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	//belongsto relationship
	public function belongstostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}

	public function belongstooptleavetype(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptLeaveType::class, 'leave_type_id');
	}

	public function belongstooptleavestatus(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptLeaveStatus::class, 'leave_status_id');
	}


	/////////////////////////////////////////////////////////////////////////////////////////
	// hasone relationship (for leave cancel)

	public function hasoneleaveapprovalbackup(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalBackup::class, 'leave_id');
	}

	public function hasoneleaveapprovalsupervisor(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'leave_id');
	}

	public function hasoneleaveapprovalhod(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalHOD::class, 'leave_id');
	}

	public function hasoneleaveapprovaldir(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalDirector::class, 'leave_id');
	}

	public function hasoneleaveapprovalhr(): HasOne
	{
		return $this->hasOne(\App\Models\HumanResources\HRLeaveApprovalHR::class, 'leave_id');
	}

}


