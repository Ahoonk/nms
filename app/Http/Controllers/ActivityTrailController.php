<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityTrailController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user?->isSuperAdmin() ? null : $user?->company_id;

        $query = ActivityLog::query()
            ->with(['user.company'])
            ->when($companyId, fn ($builder) => $builder->where('company_id', $companyId))
            ->when($request->filled('action'), fn ($builder) => $builder->where('action', $request->string('action')))
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $builder->where(function ($nested) use ($search) {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('route', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%");
                });
            })
            ->latest();

        return Inertia::render('ActivityTrail/Index', [
            'logs' => $query->paginate($request->integer('per_page', 15))->withQueryString(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'action' => $request->string('action')->toString(),
                'per_page' => $request->integer('per_page', 15),
            ],
        ]);
    }
}
