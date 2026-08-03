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
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('to_code')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('origin')->nullable();
            $table->text('destination')->nullable();
            $table->string('distance')->nullable();
            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->json('travel_sources')->nullable();
            $table->string('vehicle')->nullable();
            $table->json('other_funds')->nullable();
            $table->json('travelers')->nullable();
            $table->string('attachment')->nullable();
            $table->json('workflow_steps')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('DRAFT');
            $table->text('reject_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
