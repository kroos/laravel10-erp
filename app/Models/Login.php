<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// custom email reset password in 
// https://laracasts.com/discuss/channels/laravel/how-to-override-the-tomail-function-in-illuminateauthnotificationsresetpasswordphp
use App\Notifications\ResetPassword;
use Laravel\Sanctum\HasApiTokens;

class Login extends Authenticatable // implements MustVerifyEmail
{
	protected $connection = 'sqlite';
	protected $table = 'logins';
	use HasApiTokens, HasFactory, Notifiable;

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

	// public function getAuthIdentifierName()
	// {
	// 	return 'c_id';
	// }

	// for password
	// public function getAuthPassword()
	// {
	// 	return $this->c_headera;
	// }

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
		return $this->belongtouser->email;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	public function belongtouser()
	{
		return $this->belongsTo('App\Models\User', 'user_id');
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// for email Notifiable
	// https://laravel.com/docs/7.x/notifications
	public function routeNotificationForMail($notification)
	{
		// Return email address only...
		// return $this->belongtouser->email;

		// Return name and email address...
		return [$this->belongtouser->email => $this->belongtouser->name];
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// all acl will be done here

	/////////////////////////////////////////////////////////////////////////////////////////////////////
}
