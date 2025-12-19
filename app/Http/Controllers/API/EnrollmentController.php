<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Enroll user ke course
     */
    public function enroll(Request $request, $course_id)
    {
        // ambil user login (AMAN untuk IDE & Laravel)
        $user = Auth::user();

        // pastikan course ada
        $course = Course::findOrFail($course_id);

        // cegah double enroll
        $exists = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah terdaftar pada course ini'
            ], 409);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil enroll course',
            'data' => $enrollment
        ], 201);
    }

    /**
     * Ambil daftar course milik user
     */
    public function myCourses()
    {
        // ambil user login
        $user = Auth::user();

        $courses = Enrollment::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }
}
    