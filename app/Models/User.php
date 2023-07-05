<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $connection = 'sqlite';
	protected $table = 'users';
	use HasFactory;

	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function hasmanylogin()
	{
		return $this->hasMany('App\Models\Login', 'user_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
}
