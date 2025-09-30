<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_feature_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('feature');
            $table->dateTime('started_at');
            $table->dateTime('last_accessed_at');
            $table->unsignedInteger('access_count')->default(0);
            $table->unsignedTinyInteger('percent')->default(0);
            $table->string('state')->default('active');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_feature_progress');
    }
};

