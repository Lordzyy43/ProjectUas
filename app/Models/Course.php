<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title','category','description','thumbnail'];

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}

