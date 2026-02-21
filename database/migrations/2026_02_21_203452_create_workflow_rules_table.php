<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_rules')) {
            return;
        }

        Schema::create('workflow_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tribe_id', 'key'], 'workflow_rules_tribe_key_unq');
            $table->index(['tribe_id', 'is_active'], 'workflow_rules_tribe_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
