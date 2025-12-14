<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, $course_id)
    {
        try {
            $user = $request->user();

            $enroll = Enrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $course_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar ke course',
                'data' => $enroll
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal melakukan enrollment',
                'message' => 'Terjadi kesalahan pada database saat mendaftar course'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal melakukan enrollment',
                'message' => 'Terjadi kesalahan saat memproses enrollment'
            ], 500);
        }
    }

    public function myCourses(Request $request)
    {
        try {
            $user = $request->user();

            $courses = Enrollment::with('course')
                ->where('user_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data course berhasil diambil',
                'data' => $courses
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil data course',
                'message' => 'Terjadi kesalahan saat mengambil course user'
            ], 500);
        }
    }

    public function updateProgress(Request $request, $course_id)
    {
        try {
            $data = $request->validate([
                'progress' => 'required|integer|min:0|max:100'
            ]);

            $enroll = Enrollment::where('user_id', $request->user()->id)
                ->where('course_id', $course_id)
                ->firstOrFail();

            $enroll->update(['progress' => $data['progress']]);

            return response()->json([
                'success' => true,
                'message' => 'Progress course berhasil diperbarui',
                'data' => $enroll
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Enrollment tidak ditemukan',
                'message' => 'User belum terdaftar pada course ini'
            ], 404);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui progress',
                'message' => 'Terjadi kesalahan pada database saat update progress'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbarui progress',
                'message' => 'Terjadi kesalahan saat memperbarui progress course'
            ], 500);
        }
    }
}
