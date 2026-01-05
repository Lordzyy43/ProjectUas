<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title','description','thumbnail','course_category_id','created_by'];

    public function materials()
        {
            return $this->hasMany(Material::class);
        }

        public function quizzes()
        {
            return $this->hasMany(Quiz::class);
        }

        public function enrollments()
        {
            return $this->hasMany(Enrollment::class);
        }

        public function creator()
        {
            return $this->belongsTo(User::class, 'created_by')->withDefault();
        }

        public function category()
        {
            return $this->belongsTo(CourseCategory::class, 'course_category_id')->withDefault();
        }


}

