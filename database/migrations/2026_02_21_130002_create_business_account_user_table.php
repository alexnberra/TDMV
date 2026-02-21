<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_account_user')) {
            return;
        }

        Schema::create('business_account_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('manager');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['business_account_id', 'user_id'], 'biz_acct_user_biz_user_unq');
            $table->index(['user_id', 'role'], 'biz_acct_user_user_role_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_account_user');
    }
};
