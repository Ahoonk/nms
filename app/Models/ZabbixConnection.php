<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZabbixConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'base_url',
        'username',
        'password',
        'api_token',
        'timeout_seconds',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'api_token' => 'encrypted',
            'timeout_seconds' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
