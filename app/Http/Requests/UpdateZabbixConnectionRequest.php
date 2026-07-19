<?php

namespace App\Http\Requests;

use App\Models\ZabbixConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZabbixConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'api_token' => ['nullable', 'string'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'is_default' => ['boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
