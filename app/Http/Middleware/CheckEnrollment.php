<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Enrollment;
use Symfony\Component\HttpFoundation\Response;

class CheckEnrollment
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Ambil course_id dari route parameter
        $courseId = $request->route('course_id');

        // Jika route tidak membawa course_id → tolak
        if (!$courseId) {
            return response()->json([
                'success' => false,
                'message' => 'Course tidak valid'
            ], 400);
        }

        // Cek apakah user sudah enroll
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum terdaftar pada course ini'
            ], 403);
        }

        return $next($request);
    }
}
