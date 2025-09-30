<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_teachers(): void
    {
        $this->actingAsSuperAdmin();

        $createResponse = $this->postJson('/api/v1/admin/teachers', [
            'name' => 'Teacher One',
            'email' => 'teacher1@example.com',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.role', User::ROLE_TEACHER)
            ->assertJsonStructure(['meta' => ['temporary_password']]);

        $teacherId = $createResponse->json('data.id');

        $this->getJson('/api/v1/admin/teachers')
            ->assertOk()
            ->assertJsonPath('data.0.id', $teacherId);

        $this->patchJson("/api/v1/admin/teachers/{$teacherId}/reset-password")
            ->assertOk()
            ->assertJsonStructure(['meta' => ['temporary_password']]);

        $this->patchJson("/api/v1/admin/teachers/{$teacherId}/deactivate")
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_INACTIVE);

        $this->patchJson("/api/v1/admin/teachers/{$teacherId}/activate")
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_ACTIVE);
    }

    public function test_super_admin_can_manage_students(): void
    {
        $this->actingAsSuperAdmin();

        $createResponse = $this->postJson('/api/v1/admin/students', [
            'name' => 'Student One',
            'email' => 'student1@example.com',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.role', User::ROLE_STUDENT);

        $studentId = $createResponse->json('data.id');

        $this->putJson("/api/v1/admin/students/{$studentId}", [
            'name' => 'Student Updated',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Student Updated');

        $this->patchJson("/api/v1/admin/students/{$studentId}/reset-password")
            ->assertOk()
            ->assertJsonStructure(['meta' => ['temporary_password']]);
    }

    private function actingAsSuperAdmin(): User
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'email' => 'superadmin@example.com',
        ]);

        Sanctum::actingAs($superAdmin, ['super_admin', 'teacher', 'student']);

        return $superAdmin;
    }
}

