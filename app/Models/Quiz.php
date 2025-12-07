<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['category_id','title','description','time_limit_minutes'];
    public function category(){ return $this->belongsTo(QuizCategory::class, 'category_id');}
    public function questions(){ return $this->hasMany(QuizQuestion::class);}
}

