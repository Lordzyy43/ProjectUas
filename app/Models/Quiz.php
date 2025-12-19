<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'course_id',
        'quiz_category_id',
        'title',
        'description',
        'time_limit_minutes'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function category()
    {
        return $this->belongsTo(QuizCategory::class, 'quiz_category_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function results()
    {
        return $this->hasMany(UserQuizResult::class);
    }
}
