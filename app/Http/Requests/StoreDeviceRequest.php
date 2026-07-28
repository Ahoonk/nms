<?php

namespace App\Http\Requests;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Device::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'device_type' => ['required', Rule::enum(DeviceType::class)],
            'hostname' => ['required', 'string', 'max:255'],
            'hostgroup' => ['nullable', 'string', 'max:255'],
            'ip' => ['required', 'ip'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'mac' => ['nullable', 'string', 'max:32'],
            'os' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(DeviceStatus::class)],
            'zabbix_host_id' => ['nullable', 'string', 'max:255', 'unique:devices,zabbix_host_id'],
        ];
    }
}
