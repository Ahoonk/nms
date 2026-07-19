<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function record(
        Request $request,
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $properties = [],
    ): ActivityLog {
        $user = $request->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'company_id' => $user?->company_id,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
        ]);
    }
}
