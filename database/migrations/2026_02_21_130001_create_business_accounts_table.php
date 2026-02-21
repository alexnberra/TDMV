<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_accounts')) {
            return;
        }

        Schema::create('business_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('business_name');
            $table->string('business_type')->default('tribal_business');
            $table->string('tax_id')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('tax_exempt')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tribe_id', 'business_name', 'is_active'], 'biz_accts_tribe_name_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_accounts');
    }
};
