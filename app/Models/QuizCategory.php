<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
