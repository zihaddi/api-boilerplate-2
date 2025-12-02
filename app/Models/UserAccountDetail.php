<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserAccountDetail extends Model
{
    use HasFactory;
    
    protected $table = 'user_account_details';

    protected $fillable = [
        "user_id",
        "plan_id",
        "number_of_websites",
        "start_date",
        "renewal_date",
        "expiry_date",
        "api_key",
        "is_active",
        "is_trial",
        "is_expired",
        "status",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    } 
    
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function websites()
    {
        return $this->hasMany(Website::class);
    }


    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a unique API key
            $model->api_key = Str::random(40);
        });
    }
    

}
