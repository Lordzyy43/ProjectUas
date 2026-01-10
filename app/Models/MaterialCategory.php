<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Material;

class MaterialCategory extends Model
{

    protected $table = 'material_categories';
    
        protected $fillable = ['name', 'slug','description'];

        protected static function booted()
        {
            static::creating(function ($category) {
                if (empty($category->slug)) {
                    $category->slug = Str::slug($category->name);
                }
            });
        }

        public function materials()
        {
            return $this->hasMany(Material::class, 'material_category_id');
        }
    }
