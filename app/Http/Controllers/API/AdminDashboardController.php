<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Material;
use App\Models\Quiz;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'users'     => User::count(),
            'courses'   => Course::count(),
            'materials' => Material::count(),
            'quizzes'   => Quiz::count(),
        ]);
    }
}
