<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasTwoFactorSecret = Schema::hasColumn('users', 'two_factor_secret');
        $hasTwoFactorRecoveryCodes = Schema::hasColumn('users', 'two_factor_recovery_codes');
        $hasTwoFactorConfirmedAt = Schema::hasColumn('users', 'two_factor_confirmed_at');

        Schema::table('users', function (Blueprint $table) use ($hasTwoFactorSecret, $hasTwoFactorRecoveryCodes, $hasTwoFactorConfirmedAt) {
            if (! $hasTwoFactorSecret) {
                $table->text('two_factor_secret')->after('password')->nullable();
            }

            if (! $hasTwoFactorRecoveryCodes) {
                $table->text('two_factor_recovery_codes')->after('two_factor_secret')->nullable();
            }

            if (! $hasTwoFactorConfirmedAt) {
                $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('users', 'two_factor_secret') ? 'two_factor_secret' : null,
            Schema::hasColumn('users', 'two_factor_recovery_codes') ? 'two_factor_recovery_codes' : null,
            Schema::hasColumn('users', 'two_factor_confirmed_at') ? 'two_factor_confirmed_at' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
