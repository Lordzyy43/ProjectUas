<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, $course_id)
    {
        $user = $request->user();

        $enroll = Enrollment::firstOrCreate(
            ['user_id'=>$user->id, 'course_id'=>$course_id]
        );

        return response()->json(['message'=>'Enrolled','data'=>$enroll]);
    }

    public function myCourses(Request $request)
    {
        $user = $request->user();

        return Enrollment::with('course')->where('user_id',$user->id)->get();
    }

    public function updateProgress(Request $request, $course_id)
    {
        $request->validate(['progress'=>'required|integer']);

        $enroll = Enrollment::where('user_id',$request->user()->id)
            ->where('course_id',$course_id)
            ->firstOrFail();

        $enroll->update(['progress'=>$request->progress]);

        return response()->json(['message'=>'Updated','data'=>$enroll]);
    }
}
