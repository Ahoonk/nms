<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower($this->string('email')->toString()),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'company_id' => ['nullable', 'exists:companies,id'],
            'role' => ['required', Rule::in(collect(RoleName::cases())->map->value->all())],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
