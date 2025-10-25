<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\Api\StaffResource;
use App\Models\Staff;
use App\Support\Storage\StoragePath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StaffController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Staff::class);

        $perPage = $this->resolvePerPage($request);
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sort = $this->resolveSort($request);
        $status = $this->resolveStatusFilter($request);
        $search = trim((string) $request->query('search', ''));

        $query = Staff::query()->with('user:id,name,email');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $this->applySearch($query, $search);
        }

        if ($sort) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('last_name')->orderBy('first_name');
        }

        $staff = $query
            ->paginate($perPage)
            ->withQueryString();

        return StaffResource::collection($staff);
    }

    public function store(StaffStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Staff::class);

        $data = $request->safe()->except(['avatar']);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store(
                StoragePath::staffAvatars(),
                'public'
            );
        }

        $staff = Staff::create($data);

        $staff->load('user:id,name,email');

        return StaffResource::make($staff)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Staff $staff): StaffResource
    {
        $this->authorize('view', $staff);

        $staff->load('user:id,name,email');

        return StaffResource::make($staff);
    }

    public function update(StaffUpdateRequest $request, Staff $staff): StaffResource
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
        $staff->refresh()->load('user:id,name,email');

        return StaffResource::make($staff);
    }

    public function destroy(Request $request, Staff $staff): JsonResponse
    {
        $this->authorize('delete', $staff);

        if ($staff->avatar_path) {
            Storage::disk('public')->delete($staff->avatar_path);
        }

        $staff->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function resolvePerPage(Request $request): int
    {
        $allowed = [5, 10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }

    protected function resolveSort(Request $request): ?string
    {
        $sortable = ['first_name', 'last_name', 'email', 'status', 'hire_date'];
        $sort = $request->query('sort');

        return in_array($sort, $sortable, true) ? $sort : null;
    }

    protected function resolveStatusFilter(Request $request): ?string
    {
        $status = $request->query('status');

        return in_array($status, ['active', 'inactive'], true) ? $status : null;
    }

    protected function applySearch(Builder $query, string $search): void
    {
        $query->where(function ($builder) use ($search) {
            $builder
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('job_title', 'like', "%{$search}%");
        });
    }
}
