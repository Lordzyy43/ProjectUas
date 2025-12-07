<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\UserQuizResult;

class QuizSubmitController extends Controller
{
    public function submit(Request $request, $id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $answers = $request->input('answers');

        $correct = 0;
        foreach ($quiz->questions as $q) {
            if (isset($answers[$q->id]) && $answers[$q->id] == $q->correct_answer) {
                $correct++;
            }
        }

        $score = intval(($correct / max($quiz->questions->count(),1)) * 100);

        $result = UserQuizResult::create([
            'user_id'=>$request->user()->id,
            'quiz_id'=>$quiz->id,
            'score'=>$score,
            'answers'=>$answers
        ]);

        return response()->json([
            'score'=>$score,
            'correct'=>$correct,
            'total'=>$quiz->questions->count()
        ]);
    }
}
