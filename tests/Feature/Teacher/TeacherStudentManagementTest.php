<?php

namespace Tests\Feature\Teacher;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeacherStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_crud_students(): void
    {
        $teacher = User::factory()->teacher()->create();
        Sanctum::actingAs($teacher, ['teacher', 'student']);

        $create = $this->postJson('/api/v1/students', [
            'name' => 'Student Alpha',
            'email' => 'student-alpha@example.com',
        ])->assertCreated();

        $studentId = $create->json('data.id');

        $this->getJson('/api/v1/students')
            ->assertOk()
            ->assertJsonPath('data.0.id', $studentId);

        $this->putJson("/api/v1/students/{$studentId}", [
            'status' => User::STATUS_INACTIVE,
        ])->assertOk()
            ->assertJsonPath('data.status', User::STATUS_INACTIVE);

        $this->patchJson("/api/v1/students/{$studentId}/reset-password")
            ->assertOk()
            ->assertJsonStructure(['meta' => ['temporary_password']]);

        $this->patchJson("/api/v1/students/{$studentId}/activate")
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_ACTIVE);
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        $teacher = User::factory()->teacher()->create();
        Sanctum::actingAs($teacher, ['teacher', 'student']);

        $this->getJson('/api/v1/admin/teachers')->assertForbidden();
    }
}

