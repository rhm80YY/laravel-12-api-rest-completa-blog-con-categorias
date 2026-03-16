<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    
    // Campos permitidos para asignación masiva
    protected $fillable = [
        'name',
        'slug',
    ];
    // Relación muchos a muchos     
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
