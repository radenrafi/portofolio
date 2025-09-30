<?php

namespace App\Http\Controllers\Student;

use App\Enums\ProgressFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Progress\HitProgressRequest;
use App\Http\Resources\ProgressResource;
use App\Models\StudentFeatureProgress;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    public function __construct(private readonly ProgressService $service)
    {
    }

    public function hit(HitProgressRequest $request): ProgressResource
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validated();
        $progress = $this->service->hit($user, $data['feature'], $data['payload'] ?? []);

        return new ProgressResource($progress);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $items = StudentFeatureProgress::query()
            ->where('user_id', $userId)
            ->orderBy('feature')
            ->get();

        return ProgressResource::collection($items);
    }

    public function show(Request $request, string $feature): ProgressResource
    {
        $request->validate([
            'feature' => [Rule::in(ProgressFeature::all())],
        ]);

        $progress = StudentFeatureProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('feature', $feature)
            ->firstOrFail();

        return new ProgressResource($progress);
    }
}

