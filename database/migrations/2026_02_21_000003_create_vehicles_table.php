<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('vin', 17)->unique();
            $table->string('plate_number')->unique()->nullable();
            $table->integer('year');
            $table->string('make');
            $table->string('model');
            $table->string('color');
            $table->enum('vehicle_type', ['car', 'truck', 'suv', 'motorcycle', 'rv', 'trailer', 'commercial']);
            $table->enum('registration_status', ['active', 'expired', 'suspended', 'pending', 'cancelled'])->default('pending');
            $table->date('registration_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('title_number')->nullable();
            $table->string('lienholder_name')->nullable();
            $table->text('lienholder_address')->nullable();
            $table->boolean('is_garaged_on_reservation')->default(true);
            $table->integer('mileage')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vin', 'plate_number', 'owner_id', 'expiration_date', 'registration_status'], 'veh_vin_plate_owner_exp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
