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
            if (!$this->image) return null;
            
            // Jika path sudah mengandung http (dari seeder), langsung return
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }

            // Pastikan path tersambung dengan benar ke storage
            return url('storage/' . $this->image);
        }

        public function category()
        {
            return $this->belongsTo(MaterialCategory::class, 'material_category_id');
        }


    }