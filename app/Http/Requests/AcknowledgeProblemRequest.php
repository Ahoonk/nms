<?php

namespace App\Http\Requests;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class AcknowledgeProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::ProblemAcknowledge->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'min:1'],
            'message' => ['nullable', 'string', 'max:255'],
        ];
    }
}
