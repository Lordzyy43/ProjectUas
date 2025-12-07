<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuizQuestion;

class QuizQuestionController extends Controller
{
    public function store(Request $request, $quiz_id)
    {
        $data = $request->validate([
            'question'=>'required',
            'option_a'=>'required',
            'option_b'=>'required',
            'option_c'=>'nullable',
            'option_d'=>'nullable',
            'correct_answer'=>'required|in:a,b,c,d'
        ]);

        $data['quiz_id'] = $quiz_id;

        return QuizQuestion::create($data);
    }

    public function update(Request $request, $id)
    {
        $q = QuizQuestion::findOrFail($id);
        $q->update($request->all());
        return $q;
    }

    public function destroy($id)
    {
        QuizQuestion::findOrFail($id)->delete();
        return response()->json(['message'=>'deleted']);
    }
}
