<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['insurance', 'title', 'tribal_id', 'drivers_license', 'inspection', 'proof_of_residency', 'other']);
            $table->string('file_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->timestamp('uploaded_at');
            $table->enum('status', ['uploaded', 'processing', 'accepted', 'rejected', 'expired'])->default('uploaded');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->date('expiration_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['application_id', 'document_type', 'status'], 'docs_app_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
