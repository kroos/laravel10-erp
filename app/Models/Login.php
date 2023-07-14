<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// custom email reset password in 
// https://laracasts.com/discuss/channels/laravel/how-to-override-the-tomail-function-in-illuminateauthnotificationsresetpasswordphp
use App\Notifications\ResetPassword;

class Login extends Authenticatable // implements MustVerifyEmail
{
	protected $connection = 'mysql';
	protected $table = 'logins';
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

	 /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'username',
		// 'email',
		'password',
		'status',
	];

	 /**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
		protected $hidden = [
		'password',
		'remember_token',
	];

	 /**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
		// 'password' => 'hashed',		// this is because we are using clear text password
	];

	/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function getAuthIdentifierName()
	{
		return 'username';
	}

	// for password
	public function getAuthPassword()
	{
		return $this->password;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation hasMany/hasOne

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// db relation belongsTo
	public function belongtostaff(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// custom email reset password in 
	// https://laracasts.com/discuss/channels/laravel/how-to-override-the-tomail-function-in-illuminateauthnotificationsresetpasswordphp
	public function sendPasswordResetNotification($token)
	{
			$this->notify(new ResetPassword($token));
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
	public function getEmailForPasswordReset()
	{
		return $this->belongtostaff->email;
		// return $this->email;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// for email Notifiable
	// https://laravel.com/docs/7.x/notifications
	public function routeNotificationForMail($notification)
	{
		// Return email address only...
		// return $this->belongtouser->email;
		return [$this->belongtostaff->email => $this->belongtostaff->name];
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// all acl will be done here

	/////////////////////////////////////////////////////////////////////////////////////////////////////
}
