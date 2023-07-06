<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
	use HasFactory;
	use SoftDeletes;

	protected $connection = 'sqlite';
	protected $table = 'users';
	protected $casts = [
		'email_verified_at' => 'datetime',
	];	
	
	public function getEmailForPasswordReset()
	{
		return $this->email;
	}

	// this is important for sending email
	public function routeNotificationForMail($notification)
	{
		return $this->email;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanylogin()
	{
		return $this->hasMany('App\Models\Login', 'user_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
}
