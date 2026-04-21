<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'middle_name',
        'username',
        'email',
        'password',
        'role',
        'profile_picture',
        'dark_mode',
        'two_factor_enabled',
        'language',
        'google2fa_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get login history
     */
    public function loginLogs()
    {
        return $this->hasMany(\App\Models\LoginLog::class);
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all registration reviews made by this admin
     */
    public function registrationReviews()
    {
        return $this->hasMany(\App\Models\RegistrationReview::class, 'admin_id');
    }

    /**
     * Get all vehicle registrations created by this office user
     */
    public function createdRegistrations()
    {
        return $this->hasMany(\App\Models\VehicleRegistration::class, 'office_user_id');
    }
}
