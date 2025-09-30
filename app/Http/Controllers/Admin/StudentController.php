<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Students\StoreStudentRequest;
use App\Http\Requests\Students\UpdateStudentRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) min($request->integer('per_page', 15), 100);

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
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

        return UserResource::collection($students);
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        $providedPassword = array_key_exists('password', $data) && filled($data['password']);
        $password = $providedPassword ? $data['password'] : Str::random(12);

        $student = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => User::ROLE_STUDENT,
            'status' => $data['status'] ?? User::STATUS_ACTIVE,
        ]);

        $resource = (new UserResource($student))->additional([
            'meta' => [
                'temporary_password' => $providedPassword ? null : $password,
            ],
        ]);

        return $resource->response()->setStatusCode(201);
    }

    public function show(User $student)
    {
        $this->assertRole($student, User::ROLE_STUDENT);

        return new UserResource($student);
    }

    public function update(UpdateStudentRequest $request, User $student)
    {
        $this->assertRole($student, User::ROLE_STUDENT);

        $student->fill($request->validated() + ['role' => User::ROLE_STUDENT])->save();

        return new UserResource($student->refresh());
    }

    public function resetPassword(ResetPasswordRequest $request, User $student)
    {
        $this->assertRole($student, User::ROLE_STUDENT);

        $data = $request->validated();
        $providedPassword = array_key_exists('password', $data) && filled($data['password']);
        $newPassword = $providedPassword ? $data['password'] : Str::random(12);

        $student->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        $resource = (new UserResource($student->refresh()))->additional([
            'meta' => [
                'temporary_password' => $providedPassword ? null : $newPassword,
            ],
        ]);

        return $resource->response();
    }

    public function deactivate(User $student): UserResource
    {
        $this->assertRole($student, User::ROLE_STUDENT);

        $student->update(['status' => User::STATUS_INACTIVE]);

        return new UserResource($student->refresh());
    }

    public function activate(User $student): UserResource
    {
        $this->assertRole($student, User::ROLE_STUDENT);

        $student->update(['status' => User::STATUS_ACTIVE]);

        return new UserResource($student->refresh());
    }

    protected function assertRole(User $user, string $expected): void
    {
        abort_unless($user->role === $expected, 404, 'Resource not found.');
    }
}
