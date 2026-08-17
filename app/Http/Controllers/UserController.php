<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\Audit\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): Response
    {
        $users = User::query()
            ->with(['company', 'roles'])
            ->withCount('roles')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $users->getCollection()->transform(function (User $user): array {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $user->company_id,
                'company' => $user->company ? [
                    'id' => $user->company->id,
                    'name' => $user->company->name,
                ] : null,
                'role' => $user->role,
                'status' => $user->status,
                'is_super_admin' => $user->isSuperAdmin(),
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'mode' => 'create',
            'action' => route('users.store'),
            'method' => 'post',
            'companies' => $this->companyOptions(),
            'roleOptions' => $this->roleOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(
        StoreUserRequest $request,
        ActivityLogService $activityLogs,
    ): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        $user = User::create($data);
        $user->syncRoles($role);

        $activityLogs->record(
            $request,
            'user.created',
            $user,
            'Created user ' . $user->name,
            [
                'user' => $user->only(['id', 'name', 'email', 'company_id', 'status']),
                'role' => $role,
            ],
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'mode' => 'edit',
            'action' => route('users.update', $user),
            'method' => 'put',
            'user' => $user->load(['company', 'roles']),
            'companies' => $this->companyOptions(),
            'roleOptions' => $this->roleOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        ActivityLogService $activityLogs,
    ): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $updated = $user->fill($data);
        $updated->save();
        $updated->syncRoles($role);

        $activityLogs->record(
            $request,
            'user.updated',
            $updated,
            'Updated user ' . $updated->name,
            [
                'changes' => Arr::except($request->validated(), ['password', 'password_confirmation']),
                'role' => $role,
            ],
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(
        Request $request,
        User $user,
        ActivityLogService $activityLogs,
    ): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account from user management.');
        }

        $snapshot = $user->only(['id', 'name', 'email', 'company_id', 'status']);
        $role = $user->role;
        $user->delete();

        $activityLogs->record(
            $request,
            'user.deleted',
            $user,
            'Deleted user ' . $snapshot['name'],
            [
                'user' => $snapshot,
                'role' => $role,
            ],
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function companyOptions(): array
    {
        return $this->companies->all()
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
            ])
            ->values()
            ->all();
    }

    private function roleOptions(): array
    {
        return collect(RoleName::cases())
            ->map(fn (RoleName $role) => [
                'label' => Str::headline($role->value),
                'value' => $role->value,
            ])
            ->values()
            ->all();
    }

    private function statusOptions(): array
    {
        return collect(UserStatus::cases())
            ->map(fn (UserStatus $status) => [
                'label' => Str::headline($status->value),
                'value' => $status->value,
            ])
            ->values()
            ->all();
    }
}
