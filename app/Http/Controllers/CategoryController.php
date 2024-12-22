<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function destroyAll()
    {
        // Menghapus semua data dalam tabel
        Category::truncate();
    }
}
