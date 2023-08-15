<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

// db relation class to load
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


// class Staff extends Model
class Staff extends Authenticatable
{
	use Notifiable, HasFactory, SoftDeletes;

	protected $connection = 'mysql';
	protected $table = 'staffs';
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'id', 'status_id', 'name', 'ic', 'authorise_id', 'restday_group_id', 'religion_id', 'gender_id', 'race_id', 'nationality_id', 'marital_status_id', 'leave_flow_id', 'div_id', 'email', 'address', 'place_of_birth', 'mobile', 'phone', 'dob', 'cimb_account', 'epf_account', 'income_tax_no', 'socso_no', 'weight', 'height', 'active', 'join', 'confirmed', 'remarks', 'image'
	];

	// public function getEmailForPasswordReset()
	// {
	// 	return $this->email;
	// }

	// // this is important for sending email
	// public function routeNotificationForMail($notification)
	// {
	// 	return $this->email;
	// }

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation hasMany/hasOne
	public function hasmanylogin(): HasMany
	{
		return $this->hasMany(Login::class, 'staff_id');
	}

	public function hasmanyleave(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeave::class, 'staff_id');
	}

	public function hasmanyleaveannual(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveAnnual::class, 'staff_id');
	}

	public function hasmanyleavemc(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveMC::class, 'staff_id');
	}

	public function hasmanyleavematernity(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveMaternity::class, 'staff_id');
	}

	public function hasmanyleavereplacement(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveReplacement::class, 'staff_id');
	}

	public function hasmanyleaveapprovalbackup(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalBackup::class, 'staff_id');
	}

	public function hasmanyleaveapprovalsupervisor(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'staff_id');
	}

	public function hasmanyleaveapprovalhod(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalHOD::class, 'staff_id');
	}

	public function hasmanyleaveapprovaldir(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalDirector::class, 'staff_id');
	}

	public function hasmanyleaveapprovalhr(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveApprovalHR::class, 'staff_id');
	}

	public function hasmanyleaveamend(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRLeaveAmend::class, 'staff_id');
	}

	public function hasmanyspouse(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRStaffSpouse::class, 'staff_id');
	}

	public function hasmanychildren(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HRStaffChildren::class, 'staff_id');
	}

	public function hasmanyemergency(): HasMany
	{
		return $this->hasMany(\App\Models\HumanResources\HREmergency::class, 'staff_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsToMany
	public function belongstomanydepartment(): BelongsToMany
	{
		return $this->belongsToMany(\App\Models\HumanResources\DepartmentPivot::class, 'pivot_staff_pivotdepts', 'staff_id', 'pivot_dept_id')->withPivot('main', 'id')->withTimestamps();
	}

	public function crossbackupto(): BelongsToMany
	{
		return $this->belongsToMany(Staff::class, 'pivot_cross_backups', 'staff_id', 'backup_staff_id')->withPivot('active')->withTimestamps();
	}

	public function crossbackupfrom(): BelongsToMany
	{
		return $this->belongsToMany(Staff::class, 'pivot_cross_backups', 'backup_staff_id', 'staff_id')->withPivot('active')->withTimestamps();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation BelongsTo
	public function belongstoleaveapprovalflow(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\HRLeaveApprovalFlow::class, 'leave_flow_id');
	}

	// db relation BelongsTo
	public function belongstorestdaygroup(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptRestdayGroup::class, 'restday_group_id');
	}

	public function belongstogender(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptGender::class, 'gender_id');
	}

	public function belongstonationality(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptCountry::class, 'nationality_id');
	}

	public function belongstoreligion(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptReligion::class, 'religion_id');
	}

	public function belongstorace(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptRace::class, 'race_id');
	}

	public function belongstomaritalstatus(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptMaritalStatus::class, 'marital_status_id');
	}

	public function belongstoauthorised(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptAuthorise::class, 'authorise_id');
	}

	public function belongstodivision(): BelongsTo
	{
		return $this->belongsTo(\App\Models\HumanResources\OptDivision::class, 'div_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
}


