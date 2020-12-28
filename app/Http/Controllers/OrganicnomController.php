<?php

namespace App\Http\Controllers;

use App\Models\AppVideo;
use App\Models\OrganicnomPointer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganicnomController extends Controller
{
    public function getAllLessons()
    {
        $lessons = AppVideo::where("type", "L")
            ->where('deleted', 0)
            ->get();
        return response()->json($lessons);
    }

    public function getAllExercises()
    {
        $exercises = AppVideo::where('type', 'M')
            ->where('deleted', 0)
            ->get();
        return response()->json($exercises);
    }

    public function updatePointers(Request $request)
    {
        $userId = Auth::user()->id;
        dd($userId);

        $pointer = OrganicnomPointer::updateOrCreate(
            ['user_id' => $userId],
            [
                'exercise_pointer' => $request->input('exercise_pointer'),
                'lesson_pointer' => $request->input('lesson_pointer')
            ]
        );

        return response([
            "message" => "Success"
        ]);
    }
}
