<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\SaaS\AuditLogService;
use App\Support\Security\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GlobalAdministratorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        abort_unless(in_array($sort, ['name', 'email', 'is_active', 'created_at'], true), 404);
        $administrators = Admin::query()
            ->when($search !== '', static fn ($query) => $query->where(static fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('admin.administrator.search', compact('administrators', 'search', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.administrator.form', ['administrator' => null]);
    }

    public function show(Admin $administrator): View
    {
        return view('admin.administrator.show', compact('administrator'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validateAdministrator($request);
        $administrator = Admin::query()->create([
            'name' => $data['name'], 'email' => mb_strtolower($data['email']), 'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $audit->record('platform_admin.created', context: ['administrator_id' => $administrator->id, 'admin_id' => $request->user('admin')->id], ipAddress: $request->ip(), userAgent: $request->userAgent());

        return redirect()->route('global-admin.administrators.index')->with('status', __('global_admin.created'));
    }

    public function edit(Admin $administrator): View
    {
        $this->ensureAdministrator($administrator);

        return view('admin.administrator.form', compact('administrator'));
    }

    public function update(Request $request, Admin $administrator, AuditLogService $audit): RedirectResponse
    {
        $this->ensureAdministrator($administrator);
        $data = $this->validateAdministrator($request, $administrator);
        $isActive = (bool) ($data['is_active'] ?? false);
        $this->ensureAdminRemains($administrator, $isActive);
        $administrator->fill(['name' => $data['name'], 'email' => mb_strtolower($data['email']), 'is_active' => $isActive]);
        if (! empty($data['password'])) {
            $administrator->password = Hash::make($data['password']);
        }
        $administrator->save();
        $audit->record('platform_admin.updated', context: ['administrator_id' => $administrator->id, 'admin_id' => $request->user('admin')->id], ipAddress: $request->ip(), userAgent: $request->userAgent());

        return redirect()->route('global-admin.administrators.index')->with('status', __('global_admin.updated'));
    }

    public function destroy(Request $request, Admin $administrator, AuditLogService $audit): RedirectResponse
    {
        $this->ensureAdministrator($administrator);
        $this->ensureAdminRemains($administrator, false);
        $administrator->delete();
        $audit->record('platform_admin.removed', context: ['administrator_id' => $administrator->id, 'admin_id' => $request->user('admin')->id], ipAddress: $request->ip(), userAgent: $request->userAgent());

        return redirect()->route('global-admin.administrators.index')->with('status', __('global_admin.removed'));
    }

    private function validateAdministrator(Request $request, ?Admin $administrator = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:190', Rule::unique('admins', 'email')->ignore($administrator)],
            'password' => [$administrator ? 'nullable' : 'required', 'confirmed', PasswordPolicy::rule()], 'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function ensureAdministrator(Admin $administrator): void {}

    private function ensureAdminRemains(Admin $administrator, bool $willRemainActive): void
    {
        if (! $willRemainActive && Admin::query()->where('is_active', true)->where('id', '!=', $administrator->id)->doesntExist()) {
            abort(422, __('global_admin.last_admin'));
        }
    }
}
