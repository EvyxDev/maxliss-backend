<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'title',
        'body',
        'is_read',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

}
