<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disability_placards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('placard_number')->nullable()->unique();
            $table->enum('placard_type', ['temporary', 'permanent', 'veteran_disabled'])->default('temporary');
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'revoked'])->default('pending');
            $table->date('issued_at')->nullable();
            $table->date('expiration_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'placard_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disability_placards');
    }
};
