<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Traemos todas las categorías. Como son solo 10, all() está perfecto.
        // Si fueran miles, usaríamos cursorPaginate() como en los posts.
        $categories = Category::all();
        
        return response()->json($categories);
    }

}