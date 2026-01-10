<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'attempt',
        'question_id',
        'selected_answer',
        'is_correct'
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
