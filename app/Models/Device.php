<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'device_type',
        'hostname',
        'hostgroup',
        'ip',
        'vendor',
        'model',
        'serial_number',
        'mac',
        'os',
        'status',
        'zabbix_host_id',
    ];

    protected function casts(): array
    {
        return [
            'device_type' => DeviceType::class,
            'status' => DeviceStatus::class,
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
