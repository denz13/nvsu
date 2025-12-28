<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('model_type'); // e.g., 'App\Models\students'
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the model record
            $table->string('action'); // 'created', 'updated', 'deleted', 'restored', etc.
            $table->text('description')->nullable(); // Human-readable description
            $table->json('old_values')->nullable(); // Old values before update/delete
            $table->json('new_values')->nullable(); // New values after create/update
            $table->json('changes')->nullable(); // Only changed fields (for updates)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
