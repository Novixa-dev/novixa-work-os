<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Project Notes & Info Cards (Credentials, Plans, Notes, Custom Data)
        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('note'); // note, credential, plan, dashboard, custom
            $table->text('content')->nullable();
            $table->string('url', 2048)->nullable(); // e.g. login url or dashboard url
            $table->string('username')->nullable(); // for login / credential info card
            $table->text('password')->nullable(); // for login / credential info card (masked in UI)
            $table->string('environment')->nullable(); // production, staging, development, internal
            $table->string('color')->nullable(); // blue, amber, emerald, purple, slate
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['project_id', 'is_pinned']);
        });

        // 2. Project Reports & Suggestions (Progress updates, Suggestions, Risks, Reviews)
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('progress_report'); // progress_report, suggestion, risk, review
            $table->text('content');
            $table->string('status')->default('open'); // open, reviewed, approved, resolved
            $table->timestamps();

            $table->index(['project_id', 'type']);
        });

        // 3. Add category to project_links for categorization (dashboard, figma, repo, docs, other)
        if (Schema::hasTable('project_links') && ! Schema::hasColumn('project_links', 'category')) {
            Schema::table('project_links', function (Blueprint $table) {
                $table->string('category')->default('other')->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('project_links', 'category')) {
            Schema::table('project_links', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
        Schema::dropIfExists('project_reports');
        Schema::dropIfExists('project_notes');
    }
};
