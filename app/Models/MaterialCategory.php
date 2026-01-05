<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaterialCategory extends Model
{
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
            return $this->hasMany(Material::class);
        }
    }
