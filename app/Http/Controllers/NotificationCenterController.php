<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationCenterController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->isSuperAdmin() ? null : $request->user()?->company_id;

        $recentActivity = ActivityLog::query()
            ->with(['user.company'])
            ->when($companyId, fn ($builder) => $builder->where('company_id', $companyId))
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'title' => $log->action,
                'message' => $log->description ?: 'Activity captured from the portal trail.',
                'time' => optional($log->created_at)?->diffForHumans(),
                'tone' => str_contains($log->action, 'delete') ? 'danger' : (str_contains($log->action, 'create') ? 'success' : 'info'),
                'source' => 'activity-trail',
            ])
            ->values()
            ->all();

        return Inertia::render('Notifications/Index', [
            'summaryCards' => [
                ['label' => 'Unread', 'value' => 0, 'hint' => 'Notification center skeleton'],
                ['label' => 'Recent Activity', 'value' => count($recentActivity), 'hint' => 'From the audit trail'],
                ['label' => 'System Alerts', 'value' => 0, 'hint' => 'Reserved for Zabbix feed'],
                ['label' => 'Acknowledgements', 'value' => 0, 'hint' => 'Reserved for problem actions'],
            ],
            'notifications' => $recentActivity,
            'meta' => [
                'message' => 'Notification delivery, unread state, and channel routing will be connected in the next stage.',
            ],
        ]);
    }
}
