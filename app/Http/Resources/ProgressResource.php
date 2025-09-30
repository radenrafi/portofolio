<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\StudentFeatureProgress */
class ProgressResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'feature' => $this->feature,
            'started_at' => $this->started_at,
            'last_accessed_at' => $this->last_accessed_at,
            'access_count' => $this->access_count,
            'percent' => $this->percent,
            'state' => $this->state,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

