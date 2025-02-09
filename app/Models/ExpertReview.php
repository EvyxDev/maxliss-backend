<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'user_id',
        'rating',
        'review',
        'image',
        'id_card',
        'lab_coat',
        'efficiency',
        'personal_hygiene',
        'smell',
        'overall_appearance',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
