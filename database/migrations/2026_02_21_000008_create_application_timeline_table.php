<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('application_timeline')) {
            return;
        }

        Schema::create('application_timeline', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->text('description');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['application_id', 'created_at'], 'app_timeline_app_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_timeline');
    }
};
