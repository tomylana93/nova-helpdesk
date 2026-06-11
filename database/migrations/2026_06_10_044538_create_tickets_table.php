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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number')->unique();
            $table->string('type');
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->string('priority');
            $table->string('status');
            $table->string('subject');
            $table->text('description');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
