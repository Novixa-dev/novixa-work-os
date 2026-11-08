<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('stage')->default('lead'); // lead, qualified, proposal, negotiation, won, lost
            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('expected_close_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->string('source')->nullable(); // Inbound, Referral, Outreach, Event
            $table->text('notes')->nullable();
            $table->foreignId('won_delivery_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->timestamps();

            $table->index('stage');
            $table->index('owner_id');
            $table->index('next_follow_up_date');
        });

        Schema::create('client_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel')->default('meeting'); // meeting, call, whatsapp, email, site_visit
            $table->text('summary');
            $table->dateTime('interaction_date');
            $table->date('next_follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'interaction_date']);
            $table->index('next_follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_interactions');
        Schema::dropIfExists('opportunities');
    }
};
