<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class QuizController extends Controller
{
    public function index()
    {
        try {
            $quizzes = Quiz::with('category')->paginate(10);
            return response()->json($quizzes);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengambil data quiz',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz = Quiz::with('questions')->findOrFail($id);
            return response()->json($quiz);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengambil detail quiz',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ADMIN
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id'=>'required|exists:quiz_categories,id',
                'title'=>'required|string',
                'description'=>'nullable|string',
                'time_limit_minutes'=>'nullable|integer'
            ]);

            $quiz = Quiz::create($data);
            return response()->json(['message' => 'Quiz created', 'data' => $quiz], 201);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Gagal membuat quiz',
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $data = $request->validate([
                'category_id'=>'sometimes|required|exists:quiz_categories,id',
                'title'=>'sometimes|required|string',
                'description'=>'nullable|string',
                'time_limit_minutes'=>'nullable|integer'
            ]);

            $quiz->update($data);
            return response()->json(['message' => 'Quiz updated', 'data' => $quiz]);

        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Gagal memperbarui quiz',
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quiz->delete();
            return response()->json(['message' => 'Quiz deleted']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus quiz',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
