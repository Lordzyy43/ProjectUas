<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MaterialCategory;

class Material extends Model
{
    protected $fillable = ['course_id','material_category_id','title','content','image','order'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    protected $appends = ['image_url'];

        public function getImageUrlAttribute()
        {
            return $this->image
                ? asset('storage/' . $this->image)
                : null;
        }

        public function category()
        {
            return $this->belongsTo(MaterialCategory::class, 'material_category_id');
        }


    }