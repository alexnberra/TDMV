<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_inspections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->date('inspection_date');
            $table->enum('result', ['pending', 'pass', 'fail', 'conditional'])->default('pending');
            $table->string('facility_name')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'result', 'inspection_date'], 'vehicle_inspections_vehicle_result_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
