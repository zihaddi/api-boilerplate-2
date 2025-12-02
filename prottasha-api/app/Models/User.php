<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthenticationLoggable;


    protected $table = 'users';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "uid",
        "mobile",
        "ccode",
        "email",
        "auth_code",
        "otp_for",
        "is_verify",
        "status",
        "photo",
        "mobile_verified_at",
        "email_verified_at",
        "user_type",
        "ccode",
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function UserName()
    {
        return $this->hasOne(UserInfo::class, 'user_id')->select('user_id', 'first_name', 'last_name');
    }

    public function UserInfo()
    {
        return $this->hasOne(UserInfo::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'user_type', 'id');
    }

    public function getPhotoAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('id', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('mobile', 'like', '%' . $search . '%');
        })->when(isset($filters['role']) && $filters['role'] !== null, function ($query) use ($filters) {
            $query->where('user_type', '=', $filters['role']);
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', strtoupper($filters['status']));
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            // if ($trashed === 'with') {
            //     $query->withTrashed();
            // } elseif ($trashed === 'only') {
            //     $query->onlyTrashed();
            // }
        });
    }
}
