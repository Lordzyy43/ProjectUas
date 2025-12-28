<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class EnrollmentController extends Controller
{
    public function enroll($course_id)
    {
        try {
            $user = Auth::user();

            // 1. Pastikan course ada
            $course = Course::findOrFail($course_id);

            // 2. Cek sudah enroll atau belum
            $alreadyEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course_id)
                ->exists();

            if ($alreadyEnrolled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah terdaftar pada course ini'
                ], 409);
            }

            // 3. Simpan enrollment
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar course'
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course tidak ditemukan'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftar course'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
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
    