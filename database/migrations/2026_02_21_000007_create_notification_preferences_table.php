<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_preferences')) {
            return;
        }

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('expiration_reminders')->default(true);
            $table->boolean('status_updates')->default(true);
            $table->boolean('document_requests')->default(true);
            $table->boolean('payment_confirmations')->default(true);
            $table->boolean('office_announcements')->default(false);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
