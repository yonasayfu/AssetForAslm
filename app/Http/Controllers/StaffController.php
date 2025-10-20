<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $sort = $request->query('sort');
        $direction = $request->query('direction', 'asc');
        $perPage = $request->integer('per_page', 10);

        $query = Staff::query()->with('user:id,name,email');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        $sortable = ['first_name', 'last_name', 'email', 'status', 'hire_date'];
        if ($sort && in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('last_name')->orderBy('first_name');
        }

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

    public function store(StaffStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Staff::class);

        Staff::create($request->validated());

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

        $staff->update($request->validated());

        return redirect()
            ->route('staff.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);

        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('bannerStyle', 'info')
            ->with('banner', 'Staff member removed.');
    }
}
