<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuizCategory;
use Illuminate\Http\Request;

class QuizCategoryController extends Controller
{
    // PUBLIC
    public function index()
    {
        return QuizCategory::all();
    }

    // ADMIN
    public function store(Request $request)
    {
        $data = $request->validate(['name'=>'required','description'=>'nullable']);
        return QuizCategory::create($data);
    }

    public function update(Request $request, $id)
    {
        $cat = QuizCategory::findOrFail($id);
        $cat->update($request->all());
        return $cat;
    }

    public function destroy($id)
    {
        QuizCategory::findOrFail($id)->delete();
        return response()->json(['message'=>'deleted']);
    }
}
