<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Category::create([
            'colocation_id' => $colocation->id,
            'name' => trim($request->name),
            'color' => $request->color ?: '#10b981',
        ]);

        return back()->with('success', 'Categorie ajoutee avec succes.');
    }
}
