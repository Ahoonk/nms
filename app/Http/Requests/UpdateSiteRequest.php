<?php

namespace App\Http\Requests;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('site')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->user()?->isSuperAdmin()) {
            $this->merge([
                'company_id' => $this->route('site')?->company_id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'wireguard_ip' => ['nullable', 'ip'],
            'gateway' => ['nullable', 'ip'],
            'timezone' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(SiteStatus::class)],
        ];
    }
}
