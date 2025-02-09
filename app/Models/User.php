<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, env('ADMIN_USER_EMAIL_DOMAIN', '@northland-co.com')) && $this->hasVerifiedEmail();
    }

    public function routeNotificationForKavenegar($driver, $notification = null)
    {
        return $this->phone;
    }

    public static function rules($id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
                function ($attribute, $value, $fail) {
                    if (empty($value) && empty(request()->input('phone'))) {
                        $fail('Either email or phone is required.');
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('users')->ignore($id),
                function ($attribute, $value, $fail) {
                    if (empty($value) && empty(request()->input('email'))) {
                        $fail('Either email or phone is required.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Generate One time password for user login
     */
    public function generateOtp()
    {
        $otp = random_int(100000, 999999);
        $key = 'otp:'.$this->id;
        Redis::setex($key, 600, $otp);

        return $otp;
    }

    /**
     * Return One time password for user login
     */
    public function getOtp()
    {
        $key = 'otp:'.$this->id;

        return Redis::get($key);
    }

    /**
     * Clear One time password from cache
     */
    public function clearOtp()
    {
        $key = 'otp:'.$this->id;
        Redis::del($key);
    }

    /**
     * Set the user's phone number.
     *
     * @param  string  $value
     * @return void
     */
    public function setPhoneAttribute($value)
    {
        // Remove leading zero if it exists
        $this->attributes['phone'] = ltrim($value, '0');
    }

    /**
     * Get the user's phone number.
     *
     * @return string
     */
    public function getPhoneAttribute($value)
    {
        // Add leading zero if it doesn't exist
        return '0'.$value;
    }

    /**
     * Get the user's Default Address.
     *
     * @return \App\Models\UserAddress
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    // Getters
    public function getAvatarAttribute()
    {
        return $this->profile->avatar;
    }

    // Relations
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(UserOrder::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function cart(): HasMany
    {
        return $this->hasMany(UserCart::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function specialPricesGroups(): BelongsToMany
    {
        return $this->belongsToMany(SpecialPricesGroup::class, 'user_has_special_prices_group');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user')->withTimestamps();
    }
}
