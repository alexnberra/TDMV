<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->unique()->nullable();
            $table->enum('payment_method', ['card', 'ach', 'cash', 'check', 'money_order']);
            $table->decimal('amount', 10, 2);
            $table->json('fee_breakdown');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'disputed'])->default('pending');
            $table->string('payment_gateway')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'application_id', 'status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
