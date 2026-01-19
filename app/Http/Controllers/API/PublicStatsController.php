<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Quiz;

class PublicStatsController extends Controller
{
    public function overview()
    {
        return response()->json([
            'courseCount' => Course::count(),
            'materiCount' => Material::count(),
            'quizCount'   => Quiz::count(),
        ]);
    }
}
