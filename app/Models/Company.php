<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'logo',
        'email',
        'phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function zabbixConnections(): HasMany
    {
        return $this->hasMany(ZabbixConnection::class);
    }
}
