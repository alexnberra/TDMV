<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('provider_name');
            $table->string('policy_number');
            $table->date('effective_date');
            $table->date('expiration_date');
            $table->enum('status', ['pending', 'active', 'lapsed', 'expired', 'cancelled'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verification_source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'status', 'expiration_date'], 'ins_policies_vehicle_status_exp_idx');
            $table->unique(['vehicle_id', 'policy_number'], 'ins_policies_vehicle_policy_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
