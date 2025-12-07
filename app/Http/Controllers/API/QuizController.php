<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        return Quiz::with('category')->paginate(10);
    }

    public function show($id)
    {
        return Quiz::with('questions')->findOrFail($id);
    }

    // ADMIN
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'=>'required|exists:quiz_categories,id',
            'title'=>'required|string',
            'description'=>'nullable|string',
            'time_limit_minutes'=>'nullable|integer'
        ]);

        return Quiz::create($data);
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update($request->all());
        return $quiz;
    }

    public function destroy($id)
    {
        Quiz::findOrFail($id)->delete();
        return response()->json(['message'=>'deleted']);
    }
}
