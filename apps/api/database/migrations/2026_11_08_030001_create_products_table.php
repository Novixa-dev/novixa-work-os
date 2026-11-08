<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->string('tagline_ar')->nullable();
            $table->string('tagline_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('lifecycle_status')->default('active'); // ideation, in_development, beta, active, sunset
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('repository_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->timestamps();

            $table->index('lifecycle_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
