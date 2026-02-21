<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appointments')) {
            return;
        }

        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('office_location_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('appointment_type', [
                'dmv_office_visit',
                'road_test',
                'vehicle_inspection',
                'photo_signature_update',
                'document_review',
                'title_signing',
                'plate_pickup',
                'virtual_consultation',
            ]);
            $table->enum('status', ['requested', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show', 'rescheduled'])->default('requested');
            $table->dateTime('scheduled_for');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('confirmation_code')->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tribe_id', 'user_id', 'status', 'scheduled_for'], 'appointments_tribe_user_status_sched_idx');
            $table->index(['office_location_id', 'scheduled_for'], 'appointments_office_sched_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
