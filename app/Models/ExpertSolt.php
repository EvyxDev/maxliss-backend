<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertSolt extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_id',
        'expert_id',
        'start',
        'end',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
	
	public function booking(){
        return $this->hasMany(BookingExpert::class, 'expert_slot_id');
    }
}
