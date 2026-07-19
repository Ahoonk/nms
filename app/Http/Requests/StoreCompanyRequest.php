<?php

namespace App\Http\Requests;

use App\Enums\CompanyStatus;
use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:companies,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
        ];
    }
}
