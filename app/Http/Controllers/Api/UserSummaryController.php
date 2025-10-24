<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserSummaryResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserSummaryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100], true) ? $perPage : 10;

        $users = User::query()
            ->select(['id', 'name', 'email', 'account_status'])
            ->with('roles:id,name')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return UserSummaryResource::collection($users);
    }
}
