<?php

namespace App\Http\Requests\Progress;

use App\Enums\ProgressFeature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HitProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        return [
            'feature' => ['required', 'string', Rule::in(ProgressFeature::all())],
            'payload' => ['nullable', 'array'],
        ];
    }
}

