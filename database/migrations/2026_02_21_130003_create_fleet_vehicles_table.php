<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fleet_vehicles')) {
            return;
        }

        Schema::create('fleet_vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamp('added_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_account_id', 'vehicle_id'], 'fleet_vehicles_biz_vehicle_unq');
            $table->index(['vehicle_id', 'status'], 'fleet_vehicles_vehicle_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicles');
    }
};
