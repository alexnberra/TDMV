<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_benefits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->enum('benefit_type', ['elder', 'veteran', 'disabled', 'military_active']);
            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])->default('pending');
            $table->date('effective_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'benefit_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_benefits');
    }
};
