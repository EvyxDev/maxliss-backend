<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'expert_id',
    ];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }
}
