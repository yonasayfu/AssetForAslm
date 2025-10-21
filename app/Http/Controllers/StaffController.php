<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\Staff;
use App\Support\Exports\ExportConfig;
use App\Support\Exports\HandlesDataExport;
use App\Support\Storage\StoragePath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    use HandlesDataExport;

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort');
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedPerPage = [5, 10, 25, 50, 100];
        $perPage = $request->integer('per_page', 5);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }
        $status = $this->extractStatusFilter($request);

        $query = Staff::query()->with('user:id,name,email');

        $this->applyFilters($query, $request);
        $this->applySearch($query, $search);
        $this->applySorting($query, $request);

        $staff = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Staff $staff) => [
                'id' => $staff->id,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'job_title' => $staff->job_title,
                'status' => $staff->status,
                'avatar_url' => $staff->avatar_url,
                'user' => $staff->user ? [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                ] : null,
            ]);

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'stats' => [
                [
                    'label' => 'Total Staff',
                    'value' => Staff::count(),
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Active',
                    'value' => Staff::where('status', 'active')->count(),
                    'tone' => 'success',
                ],
                [
                    'label' => 'Inactive',
                    'value' => Staff::where('status', 'inactive')->count(),
                    'tone' => 'muted',
                ],
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
            'statuses' => [
                ['label' => 'All', 'value' => null],
                ['label' => 'Active', 'value' => 'active'],
                ['label' => 'Inactive', 'value' => 'inactive'],
            ],
            'can' => [
                'create' => $request->user()->can('staff.create'),
                'edit' => $request->user()->can('staff.update'),
                'delete' => $request->user()->can('staff.delete'),
            ],
            'breadcrumbs' => [
                ['title' => 'Staff', 'href' => route('staff.index')],
            ],
            'print' => (bool) $request->boolean('print'),
        ]);
    }

    public function export(Request $request)
    {
        return $this->handleExport($request, Staff::class, ExportConfig::staff(), [
            'label' => 'Staff Directory',
            'type' => 'staff',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Staff::class);

        return Inertia::render('Staff/Create', [
            'breadcrumbs' => [
                ['title' => 'Staff', 'href' => route('staff.index')],
                ['title' => 'Create', 'href' => route('staff.create')],
            ],
        ]);
    }

    public function show(Request $request, Staff $staff): Response
    {
        $this->authorize('view', $staff);

        $staff->load('user:id,name,email');

        $activity = $staff->activityLogs()
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Staff/Show', [
            'staff' => [
                'id' => $staff->id,
                'full_name' => $staff->full_name,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'job_title' => $staff->job_title,
                'status' => $staff->status,
                'hire_date' => optional($staff->hire_date)->toDateString(),
                'avatar_url' => $staff->avatar_url,
                'avatar_label' => $staff->avatar_path ? basename($staff->avatar_path) : null,
                'user' => $staff->user ? [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                ] : null,
            ],
            'activity' => ActivityLogResource::collection($activity),
            'breadcrumbs' => [
                ['title' => 'Staff', 'href' => route('staff.index')],
                ['title' => $staff->full_name ?: $staff->first_name, 'href' => route('staff.show', $staff)],
            ],
            'print' => (bool) $request->boolean('print'),
        ]);
    }

    public function store(StaffStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Staff::class);

        $data = $request->safe()->except(['avatar']);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store(
                StoragePath::staffAvatars(),
                'public'
            );
        }

        Staff::create($data);

        return redirect()
            ->route('staff.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'Staff member created successfully.');
    }

    public function edit(Staff $staff): Response
    {
        $this->authorize('update', $staff);

        $staff->load('user:id,name');

        $activity = $staff->activityLogs()
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'full_name' => $staff->full_name,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'job_title' => $staff->job_title,
                'status' => $staff->status,
                'hire_date' => optional($staff->hire_date)->toDateString(),
                'avatar_url' => $staff->avatar_url,
                'avatar_label' => $staff->avatar_path ? basename($staff->avatar_path) : null,
            ],
            'activity' => ActivityLogResource::collection($activity),
            'breadcrumbs' => [
                ['title' => 'Staff', 'href' => route('staff.index')],
                ['title' => $staff->full_name ?: $staff->first_name, 'href' => route('staff.edit', $staff)],
            ],
        ]);
    }

    public function update(StaffUpdateRequest $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);

        $data = $request->safe()->except(['avatar', 'remove_avatar']);

        if ($request->hasFile('avatar')) {
            if ($staff->avatar_path) {
                Storage::disk('public')->delete($staff->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store(
                StoragePath::staffAvatars(),
                'public'
            );
        } elseif ($request->boolean('remove_avatar')) {
            if ($staff->avatar_path) {
                Storage::disk('public')->delete($staff->avatar_path);
            }

            $data['avatar_path'] = null;
        }

        $staff->update($data);

        return redirect()
            ->route('staff.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);

        if ($staff->avatar_path) {
            Storage::disk('public')->delete($staff->avatar_path);
        }

        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('bannerStyle', 'info')
            ->with('banner', 'Staff member removed.');
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        $status = $this->extractStatusFilter($request);

        if ($status) {
            $query->where('status', $status);
        }
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        $term = trim((string) $search);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($term) {
            $builder
                ->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('job_title', 'like', "%{$term}%");
        });
    }

    protected function applySorting(Builder $query, Request $request): void
    {
        $sort = $request->query('sort');
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortable = ['first_name', 'last_name', 'email', 'status', 'hire_date'];

        if ($sort && in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);

            return;
        }

        $query->orderBy('last_name')->orderBy('first_name');
    }

    protected function extractStatusFilter(Request $request): ?string
    {
        $status = $request->query('status');

        return in_array($status, ['active', 'inactive'], true) ? $status : null;
    }
}

