<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];
    public function sender()
    {
        return $this->morphTo();
    }
    public function receiver()
    {
        return $this->morphTo();
    }
}
