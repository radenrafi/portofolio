<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ProgressFeature;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProgressResource;
use App\Models\StudentFeatureProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentProgressController extends Controller
{
    public function index(User $student)
    {
        $this->assertStudent($student);

        $items = StudentFeatureProgress::query()
            ->where('user_id', $student->id)
            ->orderBy('feature')
            ->get();

        return ProgressResource::collection($items);
    }

    public function show(User $student, string $feature): ProgressResource
    {
        $this->assertStudent($student);

        request()->validate(['feature' => [Rule::in(ProgressFeature::all())]]);

        $progress = StudentFeatureProgress::query()
            ->where('user_id', $student->id)
            ->where('feature', $feature)
            ->firstOrFail();

        return new ProgressResource($progress);
    }

    protected function assertStudent(User $user): void
    {
        abort_unless($user->role === User::ROLE_STUDENT, 404, 'Resource not found.');
    }
}

