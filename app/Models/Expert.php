<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Expert extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'image',
        'phone',
        'city_id',
        'state_id',
        'lat',
        'lon',
        'experience',
        'password',
        'otp',
        'price',
        'user_id'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function slots()
    {
        return $this->hasMany(ExpertSolt::class);
    }

    public function reviews()
    {
        return $this->hasMany(ExpertReview::class);
    }

    // User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(ExpertTranaction::class);
    }

    // community
    public function communities()
    {
        return $this->hasMany(Community::class, 'expert_id');
    }

    public function notifications()
    {
        return $this->hasMany(ExpertNotification::class);
    }
}
