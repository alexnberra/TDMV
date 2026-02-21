<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_interactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('portal');
            $table->string('intent')->nullable();
            $table->text('query_text');
            $table->text('response_text');
            $table->json('context')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->boolean('was_helpful')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tribe_id', 'created_at'], 'assistant_interactions_tribe_created_idx');
            $table->index(['user_id', 'created_at'], 'assistant_interactions_user_created_idx');
            $table->index(['intent', 'created_at'], 'assistant_interactions_intent_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_interactions');
    }
};
