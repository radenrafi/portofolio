<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) min($request->integer('per_page', 15), 100);

        $teachers = User::query()
            ->where('role', User::ROLE_TEACHER)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q');
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return UserResource::collection($teachers);
    }

    public function store(StoreTeacherRequest $request)
    {
        $data = $request->validated();

        $providedPassword = array_key_exists('password', $data) && filled($data['password']);
        $password = $providedPassword ? $data['password'] : Str::random(12);

        $teacher = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => User::ROLE_TEACHER,
            'status' => $data['status'] ?? User::STATUS_ACTIVE,
        ]);

        $resource = (new UserResource($teacher))->additional([
            'meta' => [
                'temporary_password' => $providedPassword ? null : $password,
            ],
        ]);

        return $resource->response()->setStatusCode(201);
    }

    public function show(User $teacher)
    {
        $this->assertRole($teacher, User::ROLE_TEACHER);

        return new UserResource($teacher);
    }

    public function update(UpdateTeacherRequest $request, User $teacher)
    {
        $this->assertRole($teacher, User::ROLE_TEACHER);

        $teacher->fill($request->validated() + ['role' => User::ROLE_TEACHER])->save();

        return new UserResource($teacher->refresh());
    }

    public function resetPassword(ResetPasswordRequest $request, User $teacher)
    {
        $this->assertRole($teacher, User::ROLE_TEACHER);

        $data = $request->validated();
        $providedPassword = array_key_exists('password', $data) && filled($data['password']);
        $newPassword = $providedPassword ? $data['password'] : Str::random(12);

        $teacher->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        $resource = (new UserResource($teacher->refresh()))->additional([
            'meta' => [
                'temporary_password' => $providedPassword ? null : $newPassword,
            ],
        ]);

        return $resource->response();
    }

    public function deactivate(User $teacher): UserResource
    {
        $this->assertRole($teacher, User::ROLE_TEACHER);

        $teacher->update(['status' => User::STATUS_INACTIVE]);

        return new UserResource($teacher->refresh());
    }

    public function activate(User $teacher): UserResource
    {
        $this->assertRole($teacher, User::ROLE_TEACHER);

        $teacher->update(['status' => User::STATUS_ACTIVE]);

        return new UserResource($teacher->refresh());
    }

    protected function assertRole(User $user, string $expected): void
    {
        abort_unless($user->role === $expected, 404, 'Resource not found.');
    }
}
