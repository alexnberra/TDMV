<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('service_type', ['renewal', 'new_registration', 'title_transfer', 'plate_replacement', 'duplicate_title']);
            $table->enum('status', ['draft', 'submitted', 'under_review', 'info_requested', 'approved', 'rejected', 'completed', 'cancelled'])->default('draft');
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->json('vehicle_data')->nullable();
            $table->json('requirements_data')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['case_number', 'user_id', 'status', 'service_type', 'submitted_at'], 'apps_case_user_status_type_sub_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
