<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertTranaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'expert_id',
        'title',
        'body',
        'amount',
        'date'
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
