<?php

use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\ProgressController as StudentProgressController;
use App\Http\Controllers\Teacher\StudentProgressController as TeacherStudentProgressController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::get('me', [AuthController::class, 'me']);
            Route::patch('change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('admin')->group(function () {
        Route::get('teachers', [AdminTeacherController::class, 'index']);
        Route::post('teachers', [AdminTeacherController::class, 'store']);
        Route::get('teachers/{teacher}', [AdminTeacherController::class, 'show']);
        Route::put('teachers/{teacher}', [AdminTeacherController::class, 'update']);
        Route::patch('teachers/{teacher}/reset-password', [AdminTeacherController::class, 'resetPassword']);
        Route::patch('teachers/{teacher}/deactivate', [AdminTeacherController::class, 'deactivate']);
        Route::patch('teachers/{teacher}/activate', [AdminTeacherController::class, 'activate']);

        Route::get('students', [AdminStudentController::class, 'index']);
        Route::post('students', [AdminStudentController::class, 'store']);
        Route::get('students/{student}', [AdminStudentController::class, 'show']);
        Route::put('students/{student}', [AdminStudentController::class, 'update']);
        Route::patch('students/{student}/reset-password', [AdminStudentController::class, 'resetPassword']);
        Route::patch('students/{student}/deactivate', [AdminStudentController::class, 'deactivate']);
        Route::patch('students/{student}/activate', [AdminStudentController::class, 'activate']);
    });

    Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
        Route::get('students', [TeacherStudentController::class, 'index']);
        Route::post('students', [TeacherStudentController::class, 'store']);
        Route::get('students/{student}', [TeacherStudentController::class, 'show']);
        Route::put('students/{student}', [TeacherStudentController::class, 'update']);
        Route::patch('students/{student}/reset-password', [TeacherStudentController::class, 'resetPassword']);
        Route::patch('students/{student}/deactivate', [TeacherStudentController::class, 'deactivate']);
        Route::patch('students/{student}/activate', [TeacherStudentController::class, 'activate']);

        // Teacher: view student progress
        Route::get('students/{student}/progress', [TeacherStudentProgressController::class, 'index']);
        Route::get('students/{student}/progress/{feature}', [TeacherStudentProgressController::class, 'show']);
    });

    // Student: hit and view own progress
    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
        Route::post('progress/hit', [StudentProgressController::class, 'hit']);
        Route::get('my/progress', [StudentProgressController::class, 'index']);
        Route::get('my/progress/{feature}', [StudentProgressController::class, 'show']);
    });
});
