<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // When the current assignee started working on the task ("taken" signal).
            $table->timestamp('claimed_at')->nullable()->after('completed_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Idempotency key so scheduled reminders are never sent twice to the same user.
            $table->string('dedupe_key')->nullable()->after('link');
            $table->unique(['user_id', 'dedupe_key']);
        });

        Schema::create('project_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('url', 2048)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_links');
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'dedupe_key']);
            $table->dropColumn('dedupe_key');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('claimed_at');
        });
    }
};
