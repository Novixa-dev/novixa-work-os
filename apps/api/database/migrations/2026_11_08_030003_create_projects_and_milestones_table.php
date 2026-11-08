<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('type')->default('internal_product'); // internal_product, client_delivery, operational
            $table->string('status')->default('active'); // planning, active, on_hold, completed, archived
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->foreignId('lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('lead_id');
            $table->index('product_id');
            $table->index('client_id');
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('contributor'); // lead, contributor, reviewer
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
