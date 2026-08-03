<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('travel_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('workflow_step');
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->boolean('to_recommend')->default(false);
            $table->boolean('to_provincial_officer')->default(false);
            $table->boolean('to_provincial_officer_two')->default(false);
            $table->boolean('to_admin_initial')->default(false);
            $table->boolean('to_admin_recommend')->default(false);
            $table->boolean('to_ard_initial')->default(false);
            $table->boolean('to_approve')->default(true);
            $table->boolean('to_code_provider')->default(false);
            $table->boolean('active')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_workflows');
    }
};
