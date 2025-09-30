<?php

namespace App\Services;

use App\Enums\ProgressFeature;
use App\Models\StudentFeatureProgress;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

class ProgressService
{
    public function hit(User $user, string $feature, array $payload = []): StudentFeatureProgress
    {
        $now = CarbonImmutable::now();

        $progress = StudentFeatureProgress::query()->firstOrCreate(
            ['user_id' => $user->id, 'feature' => $feature],
            [
                'started_at' => $now,
                'last_accessed_at' => $now,
                'access_count' => 1,
                'percent' => 0,
                'state' => 'active',
                'meta' => []
            ]
        );

        if ($progress->wasRecentlyCreated) {
            // Newly created; may enrich percent/state from payload
            [$percent, $state, $meta] = $this->compute($feature, $progress->meta ?? [], $payload);
            $progress->fill([
                'percent' => $percent,
                'state' => $state,
                'meta' => $meta,
            ])->save();
            return $progress->refresh();
        }

        // Update existing record
        [$percent, $state, $meta] = $this->compute($feature, $progress->meta ?? [], $payload);
        $progress->forceFill([
            'last_accessed_at' => $now,
            'access_count' => $progress->access_count + 1,
            'percent' => $percent,
            'state' => $state,
            'meta' => $meta,
        ])->save();

        return $progress->refresh();
    }

    /**
     * Merge payload into meta and compute percent/state for some features.
     *
     * @param array<string,mixed> $currentMeta
     * @param array<string,mixed> $payload
     * @return array{0:int,1:string,2:array<string,mixed>}
     */
    private function compute(string $feature, array $currentMeta, array $payload): array
    {
        $meta = array_merge($currentMeta, $payload);

        $percent = (int) ($meta['percent'] ?? 0);
        $state = (string) ($meta['state'] ?? 'active');

        if ($feature === ProgressFeature::PROBLEM_CHALLENGE) {
            $solved = (int) Arr::get($meta, 'solved', 0);
            $total = max(0, (int) Arr::get($meta, 'total', 0));
            if ($total > 0) {
                $percent = (int) floor(($solved / $total) * 100);
                if ($percent >= 100) {
                    $percent = 100;
                    $state = 'completed';
                } elseif ($percent < 100 && $solved > 0) {
                    $state = 'active';
                }
            }
        }

        // Clamp percent 0..100
        $percent = max(0, min(100, $percent));

        return [$percent, $state, $meta];
    }
}

