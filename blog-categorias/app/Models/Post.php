<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Post extends Model
{
    use HasFactory;
    // Campos permitidos para asignación masiva
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
    ];
    // Relación muchos a muchos     
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
