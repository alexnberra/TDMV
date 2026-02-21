<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('relationship_type', ['self', 'spouse', 'child', 'guardian', 'parent', 'sibling', 'other'])->default('other');
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_manage_minor_vehicles')->default(false);
            $table->boolean('is_minor')->default(false);
            $table->date('date_joined')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['household_id', 'user_id']);
            $table->index(['user_id', 'relationship_type', 'is_minor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};
