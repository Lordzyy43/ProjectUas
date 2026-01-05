<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserQuizResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;

class QuizResultController extends Controller
{
    /**
     * Ambil semua hasil quiz milik user yang sedang login
     */
    public function myResults()
    {
        try {
            // ambil user login (AMAN untuk IDE & Laravel)
            $user = Auth::user();

            $results = UserQuizResult::with('quiz')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil hasil quiz'
            ], 500);
        }
    }

    
}
