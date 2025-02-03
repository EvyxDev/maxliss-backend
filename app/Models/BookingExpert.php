<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingExpert extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'expert_slot_id',
        'order_id',
        'user_id',
        'date',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function expertSlot()
    {
        return $this->belongsTo(ExpertSolt::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot(){
        return $this->belongsTo(ExpertSolt::class);
    }
}
