<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('emissions_tests')) {
            return;
        }

        Schema::create('emissions_tests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->date('test_date');
            $table->enum('result', ['pending', 'pass', 'fail', 'waived'])->default('pending');
            $table->string('facility_name')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'result', 'test_date'], 'emissions_tests_vehicle_result_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emissions_tests');
    }
};
