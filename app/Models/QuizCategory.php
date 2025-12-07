<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizCategory extends Model
{
    protected $fillable = ['name','description'];
    public function quizzes(){ return $this->hasMany(Quiz::class, 'category_id');}
}

