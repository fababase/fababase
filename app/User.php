<?php

namespace App;

use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
	use HasRoles;
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [
		'photo_url',
		'is_admin',
		'is_norfab_user',
    'is_profaba_user',
    'can_read_field_trial_data'
	];

	/**
	 * Get the profile photo URL attribute.
	 *
	 * @return string
	 */
	public function getPhotoUrlAttribute()
	{
		return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'.jpg?s=200&d=mm';
	}

	/**
	 * Get if the user has a role of 'admin'
	 *
	 * @return boolean
	 */
	public function getIsAdminAttribute()
	{
		return $this->hasRole('admin');
	}

	/**
	 * Get if the user has a role of 'norfabUser'
	 *
	 * @return boolean
	 */
	public function getIsNorfabUserAttribute()
	{
		return $this->hasRole('norfabUser');
	}

	/**
	 * Get if the user has a role of 'profabaUser'
	 *
	 * @return boolean
	 */
	public function getIsProfabaUserAttribute()
	{
		return $this->hasRole('profabaUser');
  }
  
  /**
	 * Get if the user has permission to read field trial data
	 *
	 * @return boolean
	 */
	public function getCanReadFieldTrialDataAttribute()
	{
		return $this->can('read field trial data');
	}

	/**
	 * Get the oauth providers.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function oauthProviders()
	{
		return $this->hasMany(OAuthProvider::class);
	}

	/**
	 * Send the password reset notification.
	 *
	 * @param	string	$token
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
		$this->notify(new ResetPassword($token));
	}

	/**
	 * Send the email verification notification.
	 *
	 * @return void
	 */
	public function sendEmailVerificationNotification()
	{
		$this->notify(new VerifyEmail);
	}

	/**
	 * @return int
	 */
	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * @return array
	 */
	public function getJWTCustomClaims()
	{
		return [];
	}
}
