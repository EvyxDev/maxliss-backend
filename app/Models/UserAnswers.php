<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswers extends Model
{
    use HasFactory;
	
	protected $fillable = ['user_id' , 'answer_id'];
	protected $table = 'user_answers';  

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
